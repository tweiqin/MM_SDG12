#####################################################################
# IAM Role for SSM
#####################################################################
resource "aws_iam_role" "ssm_role" {
  name = "${var.project_name}-ssm-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "ec2.amazonaws.com"
        }
      }
    ]
  })
}

resource "aws_iam_role_policy_attachment" "ssm_policy" {
  role       = aws_iam_role.ssm_role.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

resource "aws_iam_role_policy_attachment" "s3_readonly" {
  role       = aws_iam_role.ssm_role.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonS3ReadOnlyAccess"
}

resource "aws_iam_role_policy" "secrets_access" {
  name = "${var.project_name}-secrets-access"
  role = aws_iam_role.ssm_role.id

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action   = "secretsmanager:GetSecretValue"
        Effect   = "Allow"
        Resource = var.db_password_secret_arn
      }
    ]
  })
}

resource "aws_iam_instance_profile" "ssm_profile" {
  name = "${var.project_name}-ssm-profile"
  role = aws_iam_role.ssm_role.name
}

#####################################################################
# Web Servers (Launch Template & ASG)
#####################################################################
resource "aws_launch_template" "web" {
  name_prefix   = "${var.project_name}-web-lt"
  image_id      = var.ami_id
  instance_type = var.instance_type


  network_interfaces {
    associate_public_ip_address = true
    security_groups             = [var.web_sg_id]
  }

  iam_instance_profile {
    name = aws_iam_instance_profile.ssm_profile.name
  }

  tag_specifications {
    resource_type = "instance"
    tags = {
      Name = "${var.project_name}-web"
      Role = "web"
    }
  }

  user_data = base64encode(<<-EOF
              #!/bin/bash
              apt-get update -y
              apt-get install -y docker.io awscli
              systemctl start docker
              systemctl enable docker
              usermod -aG docker ubuntu

              # Login to Docker Hub (if needed)
              # docker login -u ... -p ...

              # Fetch DB Password from Secrets Manager
              export DB_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${var.db_password_secret_name} --query SecretString --output text --region ${var.region})

              # Run Application Container
              docker run -d --restart=always -p 80:80 \
                -e DB_HOST=${var.db_host} \
                -e DB_PORT=${var.db_port} \
                -e DB_NAME=${var.db_name} \
                -e DB_USER=${var.db_username} \
                -e DB_PASSWORD=$DB_PASSWORD \
                -e PROJECT_NAME='MakanMystery' \
                -e ALLOWED_HOSTS='*' \
                peekachiu/makanmystery:latest
              EOF
  )
}

resource "aws_autoscaling_group" "web" {
  name                      = "${var.project_name}-web-asg"
  vpc_zone_identifier       = var.public_subnet_ids
  target_group_arns         = [var.public_target_group_arn]
  health_check_type         = "ELB"
  health_check_grace_period = 300

  desired_capacity = 2
  min_size         = 1
  max_size         = 2

  launch_template {
    id      = aws_launch_template.web.id
    version = "$Latest"
  }

  tag {
    key                 = "Name"
    value               = "${var.project_name}-web-asg"
    propagate_at_launch = true
  }

  instance_refresh {
    strategy = "Rolling"
    preferences {
      min_healthy_percentage = 50
    }
  }
}



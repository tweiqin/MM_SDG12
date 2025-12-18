# --- Load Balancer ---
# Entry point for the application. Distribution traffic across healthy instances.
resource "aws_lb" "main" {
  name               = "mm-sdg12-alb"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [var.alb_security_group_id]
  subnets            = var.public_subnet_ids # Lives in Public Subnet

  tags = {
    Name = "mm-sdg12-alb"
  }
}

resource "aws_lb_target_group" "main" {
  name     = "mm-sdg12-tg"
  port     = 80
  protocol = "HTTP"
  vpc_id   = var.vpc_id

  # Checks if the app is alive. If not, ASG kills the instance.
  health_check {
    path                = "/"
    healthy_threshold   = 2
    unhealthy_threshold = 10
  }
}

resource "aws_lb_listener" "front_end" {
  load_balancer_arn = aws_lb.main.arn
  port              = "80"
  protocol          = "HTTP"

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.main.arn
  }
}

# --- Launch Template ---
# Defines the blueprint for each EC2 instance launched by the ASG
resource "aws_launch_template" "app_lt" {
  name_prefix   = "mm-sdg12-lt-"
  image_id      = var.ami_id
  instance_type = var.instance_type

  vpc_security_group_ids = var.security_group_ids
  
  # Attach the IAM profile so GitHub Actions can talk to it via SSM
  iam_instance_profile {
    name = aws_iam_instance_profile.ssm_profile.name
  }

  # Startup Script (User Data)
  # 1. Installs Docker
  # 2. Starts Docker
  # 3. Pulls and runs the Docker Image using DB vars injected from Terraform
  user_data = base64encode(<<-EOF
              #!/bin/bash
              yum update -y
              amazon-linux-extras install docker -y
              service docker start
              usermod -a -G docker ec2-user
              
              # Pull and Run
              docker pull ${var.docker_image_uri}
              docker run -d -p 80:80 \
                -e DB_HOST=${var.db_endpoint} \
                -e DB_USER=${var.db_username} \
                -e DB_PASS=${var.db_password} \
                -e DB_NAME=${var.db_name} \
                ${var.docker_image_uri}
              EOF
  )

  lifecycle {
    create_before_destroy = true
  }
}

# --- IAM Role for SSM (Required for Migration) ---
# Allows AWS Systems Manager to control this instance (needed for 'deploy-migration' job)
resource "aws_iam_role" "ssm_role" {
  name = "mm-sdg12-ssm-role"

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

resource "aws_iam_role_policy_attachment" "ssm_core" {
  role       = aws_iam_role.ssm_role.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

resource "aws_iam_instance_profile" "ssm_profile" {
  name = "mm-sdg12-ssm-profile"
  role = aws_iam_role.ssm_role.name
}

# --- Auto Scaling Group ---
# Automatically creates/destroys instances based on load (or min/max config)
resource "aws_autoscaling_group" "app_asg" {
  vpc_zone_identifier = var.private_subnet_ids # Lives in Private Subnet
  target_group_arns   = [aws_lb_target_group.main.arn]
  
  desired_capacity = 2
  max_size         = 4
  min_size         = 1

  launch_template {
    id      = aws_launch_template.app_lt.id
    version = "$Latest"
  }

  tag {
    key                 = "Name"
    value               = "mm-sdg12-asg-instance"
    propagate_at_launch = true
  }
}

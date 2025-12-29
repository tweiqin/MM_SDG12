resource "aws_lb" "main" {
  name               = "${var.project_name}-${var.name_prefix}"
  internal           = var.internal
  load_balancer_type = "application"
  security_groups    = [var.security_group_id]
  subnets            = var.subnet_ids

  tags = {
    Name = "${var.project_name}-${var.name_prefix}"
  }
}

resource "random_id" "tg_suffix" {
  byte_length = 2
}

resource "aws_lb_target_group" "main" {
  name     = "${var.project_name}-${var.name_prefix}-tg-${random_id.tg_suffix.hex}"
  port     = var.target_port
  protocol = "HTTP"
  vpc_id   = var.vpc_id

  health_check {
    path                = "/"
    healthy_threshold   = 2
    unhealthy_threshold = 10
    matcher             = "200-499" # Accept 404s, 301s, etc. as "Healthy" (app is running)
  }

  stickiness {
    type            = "lb_cookie"
    cookie_duration = 86400
    enabled         = true
  }

  lifecycle {
    create_before_destroy = true
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



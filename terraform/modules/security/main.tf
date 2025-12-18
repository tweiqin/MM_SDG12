# --- Security Group: Load Balancer ---
# Allow internet traffic (0.0.0.0/0) to hit the Load Balancer on port 80.
resource "aws_security_group" "alb" {
  name        = "alb-sg"
  description = "Allow HTTP inbound traffic"
  vpc_id      = var.vpc_id

  ingress {
    description = "HTTP from Internet"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "alb-sg"
  }
}

# --- Security Group: App Server ---
# ONLY allow traffic from the Load Balancer (not the internet directly).
resource "aws_security_group" "app" {
  name        = "app-sg"
  description = "Allow HTTP from ALB"
  vpc_id      = var.vpc_id

  ingress {
    description     = "HTTP from ALB"
    from_port       = 80
    to_port         = 80
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "app-sg"
  }
}

# --- Security Group: Database ---
# ONLY allow traffic from the App Server.
# This ensures even if someone gets into the VPC, they can't access DB unless they are on the App Instance.
resource "aws_security_group" "db" {
  name        = "db-sg"
  description = "Allow MySQL from App"
  vpc_id      = var.vpc_id

  ingress {
    description     = "MySQL from App"
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.app.id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "db-sg"
  }
}

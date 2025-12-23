resource "aws_db_subnet_group" "main" {
  name       = "${var.project_name}-db-subnet-group"
  subnet_ids = var.subnet_ids

  tags = {
    Name = "${var.project_name}-db-subnet-group"
  }
}

#####################################################################
# Database (RDS)
#####################################################################
resource "aws_db_instance" "main" {
  identifier        = "${var.project_name}-db"
  engine            = "mysql"
  engine_version    = "8.0" # Check for latest supported version
  instance_class    = "db.t3.micro"
  allocated_storage = 20
  storage_type      = "gp2"

  db_name  = var.db_name
  username = var.db_username
  password = var.db_password

  multi_az               = true
  db_subnet_group_name   = aws_db_subnet_group.main.name
  vpc_security_group_ids = var.security_group_ids
  publicly_accessible    = false
  skip_final_snapshot    = true # For dev/demo only. Set to false for prod.

  tags = {
    Name = "${var.project_name}-db"
  }
}

resource "aws_ssm_parameter" "db_endpoint" {
  name  = "/${var.project_name}/db/endpoint"
  type  = "String"
  value = aws_db_instance.main.address
}

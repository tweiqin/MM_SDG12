# --- DB Subnet Group ---
# Defines the subnets where RDS is allowed to exist.
# By selecting the Private Subnets, we ensure the DB is not accessible from the internet.
resource "aws_db_subnet_group" "main" {
  name       = "mm-sdg12-db-subnet-group"
  subnet_ids = var.subnet_ids

  tags = {
    Name = "My DB Subnet Group"
  }
}

# --- RDS Instance ---
# The managed MySQL database instance
resource "aws_db_instance" "default" {
  allocated_storage      = 20
  db_name                = var.db_name
  engine                 = "mysql"
  engine_version         = "8.0"
  instance_class         = "db.t3.micro" # Free tier eligible (usually)
  username               = var.db_username
  password               = var.db_password
  parameter_group_name   = "default.mysql8.0"
  skip_final_snapshot    = true # Careful: Destroys data on deletion without backup!
  multi_az               = false # Set to true for HA, but false for cost saving
  db_subnet_group_name   = aws_db_subnet_group.main.name
  vpc_security_group_ids = var.security_group_ids
  
  # Ensure we can delete it easily
  deletion_protection = false 
}

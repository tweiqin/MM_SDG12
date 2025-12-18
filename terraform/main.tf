provider "aws" {
  region = var.aws_region
}

# --- Module: VPC (Networking) ---
# Creates the custom network with Public/Private subnets and NAT Gateways
module "vpc" {
  source = "./modules/vpc"
}

# --- Module: Security (Firewalls) ---
# Creates Security Groups for ALB, App, and Database
module "security" {
  source = "./modules/security"
  vpc_id = module.vpc.vpc_id
}

# --- Module: Database (RDS) ---
# Provisions a managed MySQL Database in the Private Subnet
module "database" {
  source = "./modules/database"

  vpc_id             = module.vpc.vpc_id
  subnet_ids         = module.vpc.private_db_subnets
  security_group_ids = [module.security.db_sg_id]

  db_username = var.db_username
  db_password = var.db_password
  db_name     = var.db_name
}

# --- Module: Compute (App Server) ---
# Creates Load Balancer and Auto Scaling Group to run the Docker App
module "compute" {
  source = "./modules/compute"

  vpc_id                = module.vpc.vpc_id
  ami_id                = var.ami_id
  instance_type         = var.instance_type
  docker_image_uri      = var.docker_image_uri
  
  public_subnet_ids     = module.vpc.public_subnets
  private_subnet_ids    = module.vpc.private_app_subnets
  alb_security_group_id = module.security.alb_sg_id
  security_group_ids    = [module.security.app_sg_id]

  # DB Connection Injection
  # Passes the RDS endpoint (created above) to the App instances
  db_endpoint = module.database.db_endpoint
  db_username = var.db_username
  db_password = var.db_password
  db_name     = var.db_name
}

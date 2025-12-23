# Terraform Configuration
terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = ">= 5.0.0"
    }
    http = {
      source  = "hashicorp/http"
      version = ">= 3.0.0"
    }
  }
}

# Root Providers
provider "aws" {
  region = var.region
}

# Virtual Private Cloud
module "vpc" {
  source       = "./modules/vpc"
  project_name = var.project_name
  vpc_cidr     = var.vpc_cidr
}

# Security Groups
module "security" {
  source       = "./modules/security"
  project_name = var.project_name
  vpc_id       = module.vpc.vpc_id
}

# Secrets Manager
module "secrets" {
  source       = "./modules/secrets"
  project_name = var.project_name
  db_password  = var.db_password
}

# RDS Database
module "database" {
  source             = "./modules/database"
  project_name       = var.project_name
  subnet_ids         = module.vpc.private_subnet_ids
  security_group_ids = [module.security.rds_sg_id]
  db_name            = var.db_name
  db_username        = var.db_username
  db_password        = var.db_password
}

data "aws_ami" "ubuntu" {
  most_recent = true
  owners      = ["099720109477"] # Canonical

  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd/ubuntu-jammy-22.04-amd64-server-*"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }
}

# EC2 Instances
module "compute" {
  source        = "./modules/compute"
  project_name  = var.project_name
  region        = var.region
  instance_type = var.instance_type
  ami_id        = data.aws_ami.ubuntu.id
  app_version   = var.app_version
  docker_image  = var.docker_image
  # ssh_key_name removed as we use SSM
  public_subnet_ids       = module.vpc.public_subnet_ids
  private_subnet_ids      = module.vpc.private_subnet_ids
  web_sg_id               = module.security.web_sg_id
  public_target_group_arn = module.public_alb.target_group_arn

  # Database Connection
  db_host     = module.database.db_address
  db_port     = module.database.db_port
  db_name     = var.db_name
  db_username = var.db_username

  db_password_secret_arn  = module.secrets.secret_arn
  db_password_secret_name = module.secrets.secret_name
}

# Public Load Balancers
module "public_alb" {
  source            = "./modules/alb"
  project_name      = var.project_name
  vpc_id            = module.vpc.vpc_id
  subnet_ids        = module.vpc.public_subnet_ids
  security_group_id = module.security.alb_sg_id

  name_prefix = "public-alb"
}

# S3 Storage (Static Assets)
module "storage" {
  source        = "./modules/storage"
  project_name  = var.project_name
  assets_dir    = "${path.module}/../frontend/public/assets/images"
  upload_sql    = true
  sql_file_path = "${path.module}/../mm_sdg12.sql"
}

# CloudWatch Monitoring
module "monitoring" {
  source       = "./modules/monitoring"
  project_name = var.project_name
  region       = var.region

  web_asg_name = module.compute.web_asg_name

  db_instance_id = module.database.db_instance_id

  public_alb_arn_suffix = module.public_alb.alb_arn_suffix
  public_tg_arn_suffix  = module.public_alb.target_group_arn_suffix
}

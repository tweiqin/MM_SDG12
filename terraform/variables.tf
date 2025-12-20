#####################################################################
# Root Configuration
#####################################################################
variable "region" {
  description = "AWS Region"
  type        = string
  default     = "ap-southeast-1"
}

variable "project_name" {
  description = "Project Name"
  type        = string
  default     = "makanmystery"
}

variable "domain_name" {
  description = "Domain Name"
  type        = string
  # default     = "makanmystery.click"
}

#####################################################################
# Virtual Private Cloud (VPC)
#####################################################################
variable "vpc_cidr" {
  description = "VPC CIDR"
  type        = string
  default     = "10.0.0.0/16"
}

#####################################################################
# Compute (EC2)
#####################################################################
variable "instance_type" {
  description = "EC2 Instance Type"
  type        = string
  default     = "t2.small"
}

variable "ami_id" {
  description = "AMI ID (Amazon Linux 2)"
  type        = string
  default     = "ami-0c55b159cbfafe1f0" # Update this for your region!
}


#####################################################################
# Database (RDS)
#####################################################################
variable "db_name" {
  description = "Database Name"
  type        = string
  default     = "makanmystery_db"
}

variable "db_username" {
  description = "Database Username"
  type        = string
}

variable "db_password" {
  description = "Database Password"
  type        = string
  sensitive   = true
}

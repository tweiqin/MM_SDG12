variable "region" {
  description = "AWS Region"
  type        = string
}

variable "project_name" {
  description = "Project name"
  type        = string
}

variable "instance_type" {
  description = "EC2 instance type"
  type        = string
  default     = "t2.micro"
}

variable "ami_id" {
  description = "AMI ID for the EC2 instances"
  type        = string
}

variable "app_version" {
  description = "Application Version (Git SHA)"
  type        = string
}

variable "docker_image" {
  description = "Docker Image to deploy"
  type        = string
}

variable "public_subnet_ids" {
  description = "List of public subnet IDs"
  type        = list(string)
}

variable "private_subnet_ids" {
  description = "List of private subnet IDs"
  type        = list(string)
}

variable "web_sg_id" {
  description = "Security Group ID for Web Servers"
  type        = string
}

variable "public_target_group_arn" {
  description = "ARN of the Public ALB Target Group"
  type        = string
}

# DB Variables
variable "db_host" {
  description = "Database Host Address"
  type        = string
}

variable "db_port" {
  description = "Database Port"
  type        = number
}

variable "db_name" {
  description = "Database Name"
  type        = string
}

variable "db_username" {
  description = "Database Username"
  type        = string
}

variable "db_password_secret_arn" {
  description = "ARN of the Database Password Secret"
  type        = string
}

variable "db_password_secret_name" {
  description = "Name of the Database Password Secret"
  type        = string
}

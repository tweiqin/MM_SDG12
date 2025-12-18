variable "vpc_id" {
  type = string
}

variable "ami_id" {
  type = string
}

variable "instance_type" {
  type    = string
  default = "t2.micro"
}

variable "public_subnet_ids" {
  description = "Subnets for ALB"
  type        = list(string)
}

variable "private_subnet_ids" {
  description = "Subnets for ASG"
  type        = list(string)
}

variable "security_group_ids" {
  description = "Security Group for the Instances"
  type        = list(string)
}

variable "alb_security_group_id" {
  description = "Security Group for the ALB"
  type        = string
}

variable "docker_image_uri" {
  type = string
}

# DB Config for injection
variable "db_endpoint" { type = string }
variable "db_username" { type = string }
variable "db_password" { type = string }
variable "db_name" { type = string }

variable "aws_region" {
  description = "The AWS region to deploy to"
  type        = string
  default     = "ap-southeast-1"
}

variable "ami_id" {
  description = "The AMI ID to use for the instance"
  type        = string
}

variable "instance_type" {
  description = "The type of instance to start"
  type        = string
  default     = "t2.micro"
}

variable "docker_image_uri" {
  description = "The URI of the Docker image to pull"
  type        = string
}

# Database Variables
variable "db_username" {
  description = "The master username for the database"
  type        = string
}

variable "db_password" {
  description = "The master password for the database"
  type        = string
  sensitive   = true
}

variable "db_name" {
  description = "The name of the database to connect to"
  type        = string
}

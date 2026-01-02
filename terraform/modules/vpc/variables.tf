variable "vpc_cidr" {
  description = "CIDR block for the VPC"
  type        = string
  default     = "10.0.0.0/16"
}

variable "project_name" {
  description = "Project name to be used for tagging resources"
  type        = string
}

variable "region" {
  description = "AWS Region"
  type        = string
}

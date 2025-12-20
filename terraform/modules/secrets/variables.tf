variable "project_name" {
  description = "Project Name"
  type        = string
}

variable "db_password" {
  description = "Database Password to store in Secrets Manager"
  type        = string
  sensitive   = true
}

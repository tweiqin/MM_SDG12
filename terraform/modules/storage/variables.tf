variable "project_name" {
  description = "Project name for naming resources"
  type        = string
}



variable "upload_sql" {
  description = "Whether to upload an SQL file"
  type        = bool
  default     = false
}

variable "sql_file_path" {
  description = "Path to the SQL file to upload"
  type        = string
  default     = ""
}

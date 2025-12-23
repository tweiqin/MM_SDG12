variable "project_name" {
  description = "Project name for naming resources"
  type        = string
}

variable "assets_dir" {
  description = "Local path to static assets to upload to S3"
  type        = string
  default     = ""
  default     = ""
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

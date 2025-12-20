variable "project_name" {
  description = "Project name for naming resources"
  type        = string
}

variable "assets_dir" {
  description = "Local path to static assets to upload to S3"
  type        = string
  default     = ""
}

resource "aws_secretsmanager_secret" "db_password" {
  name        = "/${var.project_name}/db/password"
  description = "Database password for ${var.project_name}"

  # Allow deletion without recovery for this demo/dev environment
  recovery_window_in_days = 0

  tags = {
    Name    = "/${var.project_name}/db/password"
    Project = var.project_name
  }
}

resource "aws_secretsmanager_secret_version" "db_password" {
  secret_id     = aws_secretsmanager_secret.db_password.id
  secret_string = var.db_password
}

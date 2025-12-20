output "secret_arn" {
  value = aws_secretsmanager_secret.db_password.arn
}

output "secret_name" {
  value = aws_secretsmanager_secret.db_password.name
}

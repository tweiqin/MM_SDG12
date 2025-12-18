output "db_endpoint" {
  value = split(":", aws_db_instance.default.endpoint)[0]
}

output "web_server_public_ip" {
  description = "Public IP address of the EC2 web server"
  value       = aws_instance.web_server.public_ip
}

output "web_server_public_dns" {
  description = "Public DNS of the EC2 web server"
  value       = aws_instance.web_server.public_dns
}

output "rds_endpoint" {
  description = "Endpoint of the RDS database"
  value       = aws_db_instance.default.endpoint
}

output "rds_port" {
  description = "Port of the RDS database"
  value       = aws_db_instance.default.port
}

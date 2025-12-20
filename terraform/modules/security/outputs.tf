output "alb_sg_id" {
  description = "ID of the ALB Security Group"
  value       = aws_security_group.alb.id
}

output "web_sg_id" {
  description = "ID of the Web Server Security Group"
  value       = aws_security_group.web.id
}

output "api_sg_id" {
  description = "ID of the API Server Security Group"
  value       = aws_security_group.api.id
}

output "internal_alb_sg_id" {
  description = "ID of the Internal ALB Security Group"
  value       = aws_security_group.internal_alb.id
}

output "rds_sg_id" {
  description = "ID of the RDS Security Group"
  value       = aws_security_group.rds.id
}

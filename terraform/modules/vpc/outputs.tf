output "vpc_id" {
  value = aws_vpc.main.id
}

output "public_subnets" {
  value = [aws_subnet.public_a.id, aws_subnet.public_b.id]
}

output "private_app_subnets" {
  value = [aws_subnet.private_app_a.id, aws_subnet.private_app_b.id]
}

output "private_db_subnets" {
  value = [aws_subnet.private_db_a.id, aws_subnet.private_db_b.id]
}

output "application_url" {
  description = "The DNS name of the Load Balancer"
  value       = "http://${module.compute.alb_dns_name}"
}

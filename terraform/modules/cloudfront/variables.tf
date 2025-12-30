variable "project_name" {
  description = "Project name"
  type        = string
}

variable "alb_dns_name" {
  description = "DNS name of the origin ALB"
  type        = string
}

variable "web_acl_arn" {
  description = "ARN of the WAF Web ACL (must be Global/US-East-1)"
  type        = string
}

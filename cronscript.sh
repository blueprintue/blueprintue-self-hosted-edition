#!/usr/bin/env sh
# purge sessions
echo "Job started (purge sessions): $(date)"
curl --silent http://localhost:8000/cron/purge_sessions/ &>/dev/null
echo "Job started: $(date)"
# purge users not confirmed
echo "Job started (purge users not confirmed): $(date)"
curl --silent http://localhost:8000/cron/purge_users_not_confirmed/ &>/dev/null
echo "Job started: $(date)"
# set soft delete anonymous private blueprints
echo "Job started (set soft delete anonymous private blueprints): $(date)"
curl --silent http://localhost:8000/cron/set_soft_delete_anonymous_private_blueprints/ &>/dev/null
echo "Job started: $(date)"
# purge expired forgot password token
echo "Job started (remove expired forgot password token after 1 hour): $(date)"
curl --silent http://localhost:8000/cron/purge_expired_forgot_password_token/ &>/dev/null
echo "Job started: $(date)"
# purge deleted blueprints
echo "Job started (purge deleted blueprints): $(date)"
curl --silent http://localhost:8000/cron/purge_deleted_blueprints/ &>/dev/null
echo "Job started: $(date)"
# purge rate limit entries
echo "Job started (purge rate limit entries after 1 day): $(date)"
curl --silent http://localhost:8000/cron/purge_rate_limit_entries/ &>/dev/null
echo "Job started: $(date)"
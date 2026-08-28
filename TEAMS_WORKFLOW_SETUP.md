# Teams daily notification

## Teams Workflow

1. In Teams, create a workflow with the trigger **When a Teams webhook request is received**.
2. Choose the target group chat or channel.
3. Set this request-body JSON schema:

```json
{
  "type": "object",
  "properties": {
    "team": { "type": "string" },
    "today": { "type": "string" },
    "presenter": {
      "type": "object",
      "properties": {
        "id": { "type": "string" },
        "name": { "type": "string" }
      }
    },
    "message": { "type": "string" }
  }
}
```

4. Add **Post message in a chat or channel** and use the `message` field from the webhook body.
5. Copy the generated webhook URL.

## GitHub Actions secrets

Configure these repository secrets under **Settings > Secrets and variables > Actions**:

| Secret | Value |
|---|---|
| `DAILY_TEAM` | `hcmpg` |
| `DAILY_PASSWORD` | Team password configured in the app |
| `TEAMS_FLOW_WEBHOOK` | Teams Workflow webhook URL |

The workflow runs at 17:55 UTC, equivalent to 14:55 BRT, Monday through Friday. Run it manually from the Actions tab to test the complete flow.

The GitHub Action opens the site in Chromium before calling the API. This is required because the free host's anti-bot challenge blocks direct HTTP clients.

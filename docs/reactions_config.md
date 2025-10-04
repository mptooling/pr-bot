## You can configure slack reaction via environment variables.

Change .env.dev file if you are using local environment.
For production environment change .env.prod file.

### Environment variables

```text
    SLACK_REACTIONS_ENABLED=true # Set to true to enable reactions. Make sure bot has permissions.
    SLACK_REACTION_NEW_PR=rocket # Emoji for new PR.
    SLACK_REACTION_MERGED_PR=white_check_mark # Emoji for merged PR.
    SLACK_REACTION_CLOSED_PR=no_entry_sign # Emoji for closed PR.
    SLACK_REACTION_PR_APPROVED=white_check_mark # Emoji for approved PR.
    SLACK_REACTION_PR_COMMENTED=speech_balloon # Emoji for commented PR.
    SLACK_REACTION_PR_REQUEST_CHANGE=exclamation # Emoji for PR with changes request.
```
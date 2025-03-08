## GH Webhook registration

To install the GitHub webhook, you need to create a webhook in the GitHub repository settings.

1. Navigate to Your Repository Settings:
   • Go to your GitHub account and select the repository where you want to add the webhook.
   • Click on the “Settings” tab located at the top of the repository page.
2. Access the Webhooks Section:
   • In the sidebar on the left, click on “Webhooks.”
3. Add a New Webhook:
   • Click the “Add webhook” button on the right side of the page.
4. Configure the Webhook:
   • Payload URL: Enter your application URL where you want GitHub to send the payloads. Don't forget to add `/webhook/github` route at the end.
   • Content type: application/json.
   • Secret: Provide a secret key to ensure that payloads are sent from GitHub. The same key should be set as `GITHUB_WEBHOOK_SECRET` environment variable.
5. Select Events:
   • Pick `Let me select individual events` option and select `Pull requests` events.
6. Activate the Webhook:
   • Ensure the “Active” checkbox is selected to make the webhook active immediately after creation. ￼
7. Finalize:
   • Click the “Add webhook” button to save and activate the webhook. ￼

Once the webhook is set up, GitHub will send a ping event to the specified payload URL to verify the configuration.

For more detailed information, refer to GitHub’s [official documentation on creating webhooks](https://docs.github.com/en/developers/webhooks-and-events/creating-webhooks)
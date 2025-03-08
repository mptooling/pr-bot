# Configuring application notifications
To configure notification routing between GitHub and Slack, you need to add:
- GitHub repository from which the webhook is received.
- Slack channel ID where the notifications will be sent.
- Slack mentions for the notification message.

The GitHub repository must be in the format `owner/repository`.

The Slack channel ID example `C08ED4B412G`. You can find the channel ID by checking the description of the channel. 

The Slack mentions can be user mentions `<@USERID>` or group mentions `<!subteam^GROUPID>`.
The user id can be found by right-clicking on the user and selecting `Copy member ID`.
The group id is a bit trickier to identify. Personal recommendation is to open slack in browser open the channel where 
the group is mentioned and inspect the element. The group id has format `S12312312`. To make group mention work
use the following format `<!subteam^S12312312>`.

## Add GitHub and Slack data to the database
Add GitHub and Slack data to the database:
 ```sh
    php bin/console gsm:write owner/repository SLACK_CHANNEL_ID '<@USERID>,<!subteam^GROUPID>'
 ```

## List GitHub and Slack data from the database
List GitHub and Slack data from the database:
 ```sh
    php bin/console gsm:list
 ```
Result example:
```text
+-------------------------------+---------------+--------------------------------------+
| Repository                    | Slack Channel | Mentions                             |
+-------------------------------+---------------+--------------------------------------+
| owner/repository              | SLACK_CHANID  | <@U08ENEE1ZPW>, <!subteam^S12312312> |
+-------------------------------+---------------+--------------------------------------+
```

## Remove GitHub and Slack data from the database
Remove GitHub and Slack data from the database:
 ```sh
    php bin/console gsm:remove owner/repository
 ```
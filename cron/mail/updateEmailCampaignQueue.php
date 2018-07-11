<?php

require_once (realpath('../../../src/boot.php'));

/** TODO REMOVE - Related to MANDRILL services not needed with AWS
 * Class container used to update emailCampaignQueues after messages have been sent.
 *
 * Class processManager
 */
class cronTask
{
    /** TODO Testing, unsent messages step #2
     * call to send next heap of unsent messages
     */
    public function execute()
    {
        try {
            self::updateQueuedMessages();
        }catch (\Throwable $e) {
        }catch (\Exception $e) {

        }
    }

    // TODO REMOVE, OLD functions remove after verifying unneeded or rewrite to work with current code
    /**
     * Mandril message cleanup
     */
    public static function updateQueuedMessages()
    {
        $endDateDT = new DateTime(date(util::DB_TIME_FORMAT));
        $endDateDT->sub(new DateInterval('PT1H')); //one hour

        $endDate = $endDateDT->format(util::DB_TIME_FORMAT);

        $startDateDT = new DateTime($endDate);
        $startDateDT->sub(new DateInterval('P14D')); // 14 days
        $startDate = $startDateDT->format(util::DB_TIME_FORMAT);

        $limit = 10;

        $queuedMessages = marketing::getEmailCampaignMessageColl();
        $queuedMessages->loadQueuedMessages($startDate, $endDate, $limit);

        syslog(LOG_INFO, "Updating queued messages between " . $startDate . " and " . $endDate);

        /** @var \marketing_emailCampaign_message_collection_item $queuedMessage */
        foreach ($queuedMessages as $queuedMessage) {
            try {
                $results = provider::getMandrillEmailProvider()->getEmail($queuedMessage->messageId);
                $state = $results['state'];
                $queuedMessage->state = $state;
                $queuedMessage->save();

            } catch (Exception $e) {
                syslog(LOG_ERR, "Could not update message details: " . $e->getMessage());
            }
        }
    }
}

$task = new cronTask();
//$task->execute();
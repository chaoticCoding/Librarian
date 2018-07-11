<?php

require_once (realpath('../../../src/boot.php'));

/** Manual fire test 2018-04-19 10:45am est
 * Class container for emailCampaigns getting ready to be sent.
 *
 * Class processManager
 */
class cronTask
{
    /** How far back to check for pending email campaigns needing to be built */
    const EMAILCAMPAIGN_BACKLOGPROCESSING = "-2 days";

    /** How far ahead to check for pending email campaigns needing to be built  */
    const EMAILCAMPAIGN_FUTUREPROCESSING = "+300 seconds";

    const LIMIT = 1;

    /** TODO Testing, unsent messages step #2
     * call to send next heap of unsent messages
     */
    public function execute()
    {
        if(\util::canSendEmail() ) {
            try {
                self::queueEmailCampaignBuilds();
            } catch (\Throwable $e) {
            } catch (\Exception $e) {

            }
        }
    }

    /** TODO Testing, Email Campaign Build Step #3
     *
     * Queue emailcampaign build requests to be added to SQS
     */
    public static function queueEmailCampaignBuilds()
    {
        syslog(LOG_INFO, "starting email campaign build queue");
        //echo "starting email campaign build queue";

        $to = new \DateTime();
        $from = clone $to;

        $from->modify(self::EMAILCAMPAIGN_BACKLOGPROCESSING);
        $to->modify(self::EMAILCAMPAIGN_FUTUREPROCESSING);

        // Collects next campaigns needed to build
        $emailCampaigns = \marketing::getEmailCampaignColl();
        $emailCampaigns->loadBuildable(self::LIMIT, $from, $to);

        // Triggers build request
        /** @var \marketing_emailCampaign_collection_item $emailCampaign */
        foreach ($emailCampaigns as $emailCampaign) {
            echo " - scheduling campaign " . $emailCampaign->id;
            //$emailCampaign->queueBuild();
            $emailCampaign->startBuild();
        }
    }
}

$task = new cronTask();
$task->execute();


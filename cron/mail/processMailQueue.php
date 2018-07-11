<?php

require_once (realpath('../../../src/boot.php'));

/** Manual fire test 2018-04-19 10:45am est
 * Class container for email send processing
 *
 * Class processManager
 */
class cronTask
{
    /** How far back to check for pending messages */
    const BACKLOGPROCESSING = "-2 days";

    /** TODO Testing, unsent messages step #2
     * call to send next heap of unsent messages
     */
    public function execute()
    {
        if(\util::canSendEmail() ) {
            try {
                self::sendUnsentEmailMessages();
            } catch (\Throwable $e) {
            } catch (\Exception $e) {

            }
        }
    }

    /** TODO Testing, unsent messages step #3
     * CRON Processor for unsent messages
     *
     * @throws \Exception
     */
    public static function sendUnsentEmailMessages()
    {
        $then = microtime();
        // randomized time execution so no 2 crons tasks will start exactly at the same microsecond
        usleep ( rand ( 1000 , 1000000 ) );

        //collect the current mail processor
        $emailProvider = \provider::getCurrentEmailProvider();

        // verify that we can still send messages
        if($emailProvider->hasCapacityBeenReached () == false ) {
            // get max rate that messages can be sent at this cycle (1 sec )
            $maxSend = $emailProvider->getMaxSendRate ();
            echo "Max Send:" . $maxSend . "<br>\n";

            // collect current AWS instance for record locking
            $instanceId = trim(\core\environment\instance\aws_ec2::getInstanceID());
            echo "instance:" . $instanceId . "<br>\n";

            $taskID = \util::generateTaskId();

            // need to check backlog in case some were missed or re had capacity issues
            $sinceDate = date(\util::DB_TIME_FORMAT, strtotime(self::BACKLOGPROCESSING));
            echo "sinceDate: " . $sinceDate . "<br>\n";

            echo "Locking batch of " . $maxSend . " messages to task: " . $taskID  . " on instance " . $instanceId . "<br>\n";
            syslog(LOG_INFO, "Locking batch of " . $maxSend . " messages to task: " . $taskID  . " on instance " . $instanceId . "<br>\n");

            $emailMessages = \email::getEmailMessageColl();
            $emailMessages->lockPendingMessages($maxSend, $sinceDate, $taskID, $instanceId);

            // delay to ensure record lock has been completed
            sleep ( 2 );

            $emailMessages->loadLockedMessages($taskID, $instanceId);
            echo "Collected " . count($emailMessages) . " email messages... of max " . $maxSend . "<br>\n";
            syslog(LOG_INFO, "Collected " . count($emailMessages) . " email messages... of max " . $maxSend . "<br>\n");

            /** @var \email_message_collection_item $message */
            foreach ($emailMessages as $message) {
                echo " - Sending: " . $message->id . "<br>\n";

                $message->send($emailProvider);
                echo " - exiting state: " . $message->state . "<br>\n";
            }

        } else {
            throw new\ Exception("Send Capacity reached");
        }
        $now = microtime();
        echo sprintf("Elapsed:  %f", ($now - $then));
    }
}

$task = new cronTask();
$task->execute();

<?php

namespace App\Consumer;

use App\Service\KafkaService;
use App\Service\ReportService;
use RdKafka\Message;
use SimPod\Kafka\Clients\Consumer\ConsumerConfig;
use SimPod\Kafka\Clients\Consumer\KafkaConsumer;
use SimPod\KafkaBundle\Kafka\Configuration;
use SimPod\KafkaBundle\Kafka\Clients\Consumer\NamedConsumer;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use App\DTO\Message as MessageDTO;

use const PHP_EOL;

final class MessageConsumer implements NamedConsumer
{
    private const TIMEOUT_MS = 2000;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly string $topic,
        private readonly string $groupId,
        private readonly string $name,
        private readonly KafkaService $kafkaService,
        private readonly ReportService $reportService,
    ) {
    }

    public function run(): void
    {
        $kafkaConsumer = new KafkaConsumer($this->getConfig());

        $kafkaConsumer->subscribe([$this->topic]);

        $serializer = new PhpSerializer();

        while (true) {
            $kafkaConsumer->start(
                self::TIMEOUT_MS,
                function (Message $message) use ($kafkaConsumer, $serializer): void {
                    $envelope = $serializer->decode([
                        'body' => $message->payload,
                        'headers' => [],
                    ]);

                    /** @var MessageDTO $generateReportMessage */
                    $generateReportMessage = $envelope->getMessage();

                    echo 'generating report' . $generateReportMessage->getText() . PHP_EOL;
                    $this->reportService->generateReportFile($generateReportMessage->getText());

                    $this->kafkaService->send(
                        'report_generated',
                        ['message' => 'Report ' . $generateReportMessage->getText() . ' was generated']
                    );

                    $kafkaConsumer->commit($message);
                }
            );
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function getConfig(): ConsumerConfig
    {
        $config = new ConsumerConfig();

        $config->set(ConsumerConfig::BOOTSTRAP_SERVERS_CONFIG, $this->configuration->getBootstrapServers());
        $config->set(ConsumerConfig::ENABLE_AUTO_COMMIT_CONFIG, false);
        $config->set(ConsumerConfig::CLIENT_ID_CONFIG, $this->configuration->getClientIdWithHostname());
        $config->set(ConsumerConfig::AUTO_OFFSET_RESET_CONFIG, 'earliest');
        $config->set(ConsumerConfig::GROUP_ID_CONFIG, $this->groupId);

        return $config;
    }
}

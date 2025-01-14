<?php

declare(strict_types=1);

namespace Damax\Common\Bridge\Symfony\Bundle\Listener;

use Damax\Common\Bridge\Symfony\Bundle\Annotation\Serialize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class SerializeListener implements EventSubscriberInterface
{
    private const CONTENT_TYPE = 'json';

    private const METHOD_TO_CODE = [
        'POST' => Response::HTTP_CREATED,
    ];

    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => 'onKernelView'];
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $config = $event->getRequest()->attributes->get('_serialize');

        if (!$config instanceof Serialize || $event->getResponse()) {
            return;
        }

        $context = $config->groups() ? ['groups' => $config->groups()] : [];

        $json = $this->serializer->serialize($event->getControllerResult(), self::CONTENT_TYPE, $context);

        $code = self::METHOD_TO_CODE[$event->getRequest()->getMethod()] ?? Response::HTTP_OK;

        $event->setResponse(JsonResponse::fromJsonString($json, $code)->setEncodingOptions(JSON_UNESCAPED_UNICODE));
    }
}

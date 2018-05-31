<?php

declare(strict_types=1);

namespace Damax\Common\Bridge\Symfony\Bundle\Listener;

use Damax\Common\Bridge\Symfony\Bundle\Annotation\Deserialize;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeserializeListener implements EventSubscriberInterface
{
    private const CONTENT_TYPE = 'json';

    private $serializer;
    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator = null)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -16],
        ];
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws BadRequestHttpException
     * @throws RuntimeException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_deserialize')) {
            return;
        }

        if (self::CONTENT_TYPE !== $request->getContentType()) {
            return;
        }

        /** @var Deserialize $config */
        $config = $request->attributes->get('_deserialize');

        $context = $config->groups() ? ['groups' => $config->groups()] : [];

        try {
            $data = $this->serializer->deserialize($request->getContent(), $config->className(), self::CONTENT_TYPE, $context);
        } catch (ExceptionInterface $e) {
            throw new UnprocessableEntityHttpException('Invalid json.');
        }

        if ($config->validate()) {
            if (!$this->validator) {
                throw new RuntimeException('Validator package is not installed.');
            }

            $violations = $this->validator->validate($data);

            if (count($violations)) {
                $event->setController(function () use ($violations) {
                    return $this->errorResponse($violations);
                });
            }
        }

        $request->attributes->set($config->param(), $data);
    }

    private function errorResponse(ConstraintViolationListInterface $violations): Response
    {
        $data = $this->serializer->serialize($violations, self::CONTENT_TYPE);

        return JsonResponse::fromJsonString($data, Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/problem+json']);
    }
}
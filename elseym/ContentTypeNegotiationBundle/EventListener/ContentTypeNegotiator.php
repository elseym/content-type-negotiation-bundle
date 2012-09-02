<?php

namespace elseym\ContentTypeNegotiationBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class ContentTypeNegotiator
{
    /** @var Request */
    private $request;

    /** @var LoggerInterface */
    private $logger;

    /** @var FilterControllerEvent */
    private $event;

    /** @var array */
    private $controller;

    public function __construct(Request $request, LoggerInterface $logger)
    {
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Replaces the default controller action with one that provides data in one of the requested formats
     *
     * @param FilterControllerEvent $event
     */
    public function onController(FilterControllerEvent $event)
    {
        $this->event = $event;

        $types = $this->request->getAcceptableContentTypes();

        $controller = null;
        foreach ($types as $type) {
            if ($controller = $this->findControllerForContentType($type)) {
                $this->event->setController($controller);
                break;
            }
        }
        if (null === $controller) {
            header('HTTP/1.0 406 Not Acceptable');
            die();
        }

        $controller = $event->getController();
        $this->logger->debug('Chose controller action "' . array_pop($controller) . '"');
    }

    /**
     * @param string $type
     * @return object
     */
    private function findControllerForContentType($type)
    {
        $this->logger->debug('Trying to find controller for MIME "' . $type . '"');

        list($type, $subtype) = explode('/', $type);

        $subtype = $this->normalizeSubtype($subtype);

        // */* -> indexAction
        if ('*' == $type && '*' == $subtype) {
            return $this->getController();
        }


        if ('*' !== $subtype) {
            // ex: text/html -> indexActionHtmlText
            if ($controller = $this->findControllerWithAction($this->getControllerMethod()
                                                              . ucfirst($subtype) . ucfirst($type))) {
                return $controller;
            }
            // ex: text/html -> indexActionHtml
            return $this->findControllerWithAction($this->getControllerMethod() . ucfirst($subtype));
        }
        // ex: text/* ->  indexActionText
        return $this->findControllerWithAction($this->getControllerMethod() . ucfirst($type));
    }

    /**
     * @param string $action
     * @return object
     */
    private function findControllerWithAction($action)
    {
        $this->logger->debug('Trying method "' . $action . '"');

        if (method_exists($this->getControllerObject(), $action)) {
            return array($this->getControllerObject(), $action);
        }
        return null;
    }

    /**
     * @param $subtype
     * @return string
     */
    private function normalizeSubtype($subtype)
    {
        return implode('', array_map('ucfirst', explode('+', $subtype)));
    }

    /**
     * @return object
     */
    private function getController()
    {
        if (null === $this->controller) {
            $this->controller = $this->event->getController();
        }
        return $this->controller;
    }

    /**
     * @return object
     */
    private function getControllerObject()
    {
        $controller = $this->getController();
        return $controller[0];
    }

    /**
     * @return string
     */
    private function getControllerMethod()
    {
        $controller = $this->getController();
        return $controller[1];
    }
}
<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2014 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** Notification.php */ 
namespace Core\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Core\Listener\Events\NotificationEvent;
use Core\Log\Notification\NotificationEntity;
use Core\Log\Notification\NotificationEntityInterface;

class Notification extends AbstractPlugin
{
    const NAMESPACE_INFO = 'info';
    const NAMESPACE_WARNING = 'warning';
    const NAMESPACE_DANGER  = 'danger';
    const NAMESPACE_SUCCESS  = 'success';

    protected $namespace2priority = array(
        self::NAMESPACE_INFO => NotificationEntity::INFO,
        self::NAMESPACE_SUCCESS => NotificationEntity::NOTICE,
        self::NAMESPACE_WARNING => NotificationEntity::WARN,
        self::NAMESPACE_DANGER => NotificationEntity::ERR,
    );


    protected $priority2namespace = array(
        NotificationEntity::EMERG => self::NAMESPACE_DANGER,
        NotificationEntity::ALERT => self::NAMESPACE_DANGER,
        NotificationEntity::CRIT => self::NAMESPACE_DANGER,
        NotificationEntity::ERR => self::NAMESPACE_DANGER,
        NotificationEntity::WARN => self::NAMESPACE_WARNING,
        NotificationEntity::NOTICE => self::NAMESPACE_SUCCESS,
        NotificationEntity::INFO => self::NAMESPACE_INFO,
        NotificationEntity::DEBUG => self::NAMESPACE_INFO,
    );

    
    protected $namespace = self::NAMESPACE_INFO;
    
    protected $flashMessenger;
    protected $notificationListener;
    
    public function __construct(FlashMessenger $flashMessenger)
    {
        $this->flashMessenger = $flashMessenger;
    }

    public function setListener($listener) {
        $listener->getSharedManager()->attach('*', NotificationEvent::EVENT_NOTIFICATION_HTML, array($this,'createOutput') , 1);
        $this->notificationListener = $listener;
    }
    
    public function __invoke($message = null, $namespace = self::NAMESPACE_INFO)
    {
        if (null === $message) {
            return $this;
        }
        return $this->addMessage($message, $namespace);
    }

    public function renderMessage($message, $namespace = self::NAMESPACE_INFO)
    {
        $origNamespace = $this->flashMessenger->getNamespace();
        $this->flashMessenger
            ->setNamespace($namespace)
            ->addMessage($message)
            ->setNamespace($origNamespace);

        return $this;
    }
    
    public function addMessage($message, $namespace = self::NAMESPACE_INFO)
    {
        if (!$message instanceof NotificationEntityInterface) {
            $messageText = $message;
            $message = new NotificationEntity();
            $message->setNotification($messageText);
            $message->setPriority($this->namespace2priority[$namespace]);
        }
        $nEvent = new NotificationEvent();
        $nEvent->setNotification($message);
        $this->notificationListener->trigger(NotificationEvent::EVENT_NOTIFICATION_ADD, $nEvent);

        return $this;
    }
    
    public function info($message)
    {
        return $this->addMessage($message, self::NAMESPACE_INFO);
    }
    
    public function warning($message) 
    {
        return $this->addMessage($message, self::NAMESPACE_WARNING);
    }
    
    public function success($message) 
    {
        return $this->addMessage($message, self::NAMESPACE_SUCCESS);
    }

    public function danger($message)
    {
        return $this->addMessage($message, self::NAMESPACE_DANGER);
    }
    
    public function error($message)
    {
        return $this->addMessage($message, self::NAMESPACE_DANGER);
    }

    public function createOutput(NotificationEvent $event)
    {
        $notifications = $event->getNotifications();
        if (is_array($notifications) && !empty($notifications)) {
            foreach ($notifications as $notification) {
                $this->renderMessage($notification->getNotification(), $this->priority2namespace[$notification->getPriority()]);
            }
        }
        return $this;
    }
}


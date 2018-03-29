<?php

namespace FormBuilderBundle\EventListener;

use FormBuilderBundle\Event\MailEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Parser\MailParser;
use Pimcore\Model\Document;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MailListener implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var MailParser
     */
    protected $mailParser;

    /**
     * @var IncludeRenderer
     */
    protected $includeRenderer;

    /**
     * MailListener constructor.
     *
     * @param SessionInterface         $session
     * @param MailParser      $mailParser
     * @param IncludeRenderer $includeRenderer
     */
    public function __construct(
        SessionInterface $session,
        MailParser $mailParser,
        IncludeRenderer $includeRenderer
    ) {
        $this->session = $session;
        $this->mailParser = $mailParser;
        $this->includeRenderer = $includeRenderer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormBuilderEvents::FORM_SUBMIT_SUCCESS => ['onFormSubmit'],
        ];
    }

    /**
     * @param SubmissionEvent $event
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        $request = $event->getRequest();
        $form = $event->getForm();

        $formConfiguration = $event->getFormConfiguration();
        $emailConfiguration = $formConfiguration['email'];
        $sendCopy = $emailConfiguration['send_copy'];
        $flashBag = $this->session->getFlashBag();

        try {

            if (empty($emailConfiguration['mail_template_id'])) {
                throw new \Exception('no valid mail template given.');
            }

            $send = $this->sendForm($emailConfiguration['mail_template_id'], $form, $request->getLocale());
            if ($send === TRUE) {

                //send copy!
                if ($sendCopy === TRUE) {

                    if (empty($emailConfiguration['copy_mail_template_id'])) {
                        throw new \Exception('no valid copy mail template given.');
                    }

                    $send = $this->sendForm($emailConfiguration['copy_mail_template_id'], $form, $request->getLocale(), true);
                    if ($send !== TRUE) {
                        throw new \Exception('copy mail not sent.');
                    }
                }
            } else {
                throw new \Exception('mail not sent.');
            }
        } catch (\Exception $e) {
            $flashBag->add('error', 'error while sending mail: ' . $e->getMessage());
        }

        $this->onSuccess($event, $flashBag, $emailConfiguration['mail_template_id']);
    }

    /**
     * @param int           $mailTemplateId
     * @param FormInterface $form
     * @param               $locale
     * @param bool          $isCopy
     *
     * @throws \Exception
     * @returns bool
     */
    private function sendForm($mailTemplateId = 0, FormInterface $form, $locale, $isCopy = false)
    {
        $mailTemplate = Document\Email::getById($mailTemplateId);
        if (!$mailTemplate instanceof Document\Email) {
            return FALSE;
        }

        $mail = $this->mailParser->create($mailTemplate, $form, $locale, $isCopy);

        $mailEvent = new MailEvent($form, $mail);
        \Pimcore::getEventDispatcher()->dispatch(FormBuilderEvents::FORM_MAIL_PRE_SUBMIT, $mailEvent);

        $mail = $mailEvent->getEmail();
        $mail->send();

        return TRUE;
    }

    /**
     * @param SubmissionEvent   $event
     * @param FlashBagInterface $flashBag
     * @param                   $mailTemplateId
     *
     * @return bool
     */
    private function onSuccess(SubmissionEvent $event, $flashBag, $mailTemplateId)
    {
        $error = FALSE;
        $message = 'Success!';

        $mailTemplate = Document\Email::getById($mailTemplateId);
        if (!$mailTemplate instanceof Document\Email) {
            return FALSE;
        }

        $afterSuccess = $mailTemplate->getProperty('mail_successfully_sent');

        //get the content from a snippet
        if ($afterSuccess instanceof Document\Snippet) {
            $params['document'] = $afterSuccess;

            try {
                $message = $this->includeRenderer->render($afterSuccess, $params, FALSE);
            } catch (\Exception $e) {
                $error = TRUE;
                $message = $e->getMessage();
            }
        } //it's a redirect!
        else if ($afterSuccess instanceof Document) {
            $message = $afterSuccess->getFullPath();
            $event->setRedirectUri($afterSuccess->getFullPath());
        } //it's just a string!
        else if (is_string($afterSuccess)) {
            $message = $afterSuccess;
        }

        $flashBag->add($error ? 'error' : 'success', $message);
    }
}

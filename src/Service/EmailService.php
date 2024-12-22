<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private string $fromAddress;


    public function __construct(MailerInterface $mailer, Environment $twig,  string $fromAddress)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->fromAddress = $fromAddress;

    }

    /**
     * Function to send a mail with template
     */
    public function sendEmailWithTemplate(string $to, string $subject, string $template, array $params): void
{
    $content = $this->twig->render($template, $params);

    $email = (new Email())
        ->from($this->fromAddress)
        ->to($to)
        ->subject($subject)
        ->html($content);

    $this->mailer->send($email);
}

}

<?php
declare(strict_types=1);

namespace Invo\Controllers;

use Invo\Models\Users;

/**
 * SessionController
 *
 * Allows to authenticate users
 */
class SessionController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();

        $this->tag->setTitle('Sign Up/Sign In');
    }

    public function indexAction(): void
    {
        $this->tag->setDefault('email', 'demo');
        $this->tag->setDefault('password', 'phalcon');
    }

    /**
     * This action authenticate and logs an user into the application
     */
    public function startAction()
    {
        if ($this->request->isPost()) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            /** @var Users $user */
            $user = Users::findFirst([
                "(email = :email: OR username = :email:) AND password = :password: AND active = 'Y'",
                'bind' => [
                    'email'    => $email,
                    'password' => sha1($password),
                ],
            ]);

            if ($user) {
                $this->registerSession($user);
                $this->flash->success('Welcome ' . $user->name);

                return $this->dispatcher->forward([
                    'controller' => 'invoices',
                    'action'     => 'index',
                ]);
            }

            $this->flash->error('Wrong email/password');
        }

        return $this->dispatcher->forward([
            'controller' => 'session',
            'action'     => 'index',
        ]);
    }

    /**
     * Finishes the active session redirecting to the index
     */
    public function endAction()
    {
        $this->session->remove('auth');
        $this->flash->success('Goodbye!');

        return $this->dispatcher->forward([
            'controller' => 'index',
            'action'     => 'index',
        ]);
    }

    /**
     * Register an authenticated user into session data
     *
     * @param Users $user
     */
    private function registerSession(Users $user)
    {
        $this->session->set('auth', [
            'id'   => $user->id,
            'name' => $user->name,
        ]);
    }
}

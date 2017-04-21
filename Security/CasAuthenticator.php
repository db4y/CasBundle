<?php

namespace db4y\CasBundle\Security;

use Composer\CaBundle\CaBundle;
use db4y\CasBundle\Security\Event\FailedLoginEvent;
use db4y\CasBundle\Security\Exception\InvalidConfigurationException;
use phpCAS;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class CasAuthenticator extends AbstractGuardAuthenticator
{
    const FAILED_LOGIN_EVENTNAME = 'db4y.cas_bundle.cas_authenticator.failed_login';

    /**
     * @var array
     */
    private $casConfig;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var string
     */
    private $forbiddenRouteName;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(array $casConfig, UrlGeneratorInterface $urlGenerator, $forbiddenRouteName = '', EventDispatcherInterface $dispatcher = null)
    {
        $this->casConfig = $casConfig;
        $this->urlGenerator = $urlGenerator;
        $this->forbiddenRouteName = $forbiddenRouteName;
        $this->dispatcher = $dispatcher;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (!array_key_exists('host', $this->casConfig)) {
            throw new InvalidConfigurationException('CAS host must be configured');
        }
        if (!array_key_exists('port', $this->casConfig)) {
            throw new InvalidConfigurationException('CAS port must be configured');
        }
        if (!array_key_exists('context', $this->casConfig)) {
            throw new InvalidConfigurationException('CAS context must be configured');
        }

        $url = sprintf(
            'https://%s:%d/%s?service=%s',
                $this->casConfig['host'],
                $this->casConfig['port'],
                ltrim($this->casConfig['context'], '/'),
                urlencode($request->getUri())
        );

        return new RedirectResponse($url);
    }

    public function getCredentials(Request $request)
    {
        $this->setupCasClient();

        phpCAS::forceAuthentication();

        if (phpCAS::getUser()) {
            return phpCAS::getUser();
        }

        return null;
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @throws AuthenticationException
     *
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials);
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        try {
            $url = $this->urlGenerator->generate($this->forbiddenRouteName);
        } catch (RouteNotFoundException $e) {
            throw new InvalidConfigurationException(sprintf('Forbidden route name %s is not configured', $this->forbiddenRouteName), 0, $e);
        }

        if (null !== $this->dispatcher) {
            $this->dispatcher->dispatch(self::FAILED_LOGIN_EVENTNAME, new FailedLoginEvent(phpCAS::getUser()));
        }

        return new RedirectResponse($url);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey The provider (i.e. firewall) key
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function logout($service = null)
    {
        $this->setupCasClient();

        if (null !== $service) {
            phpCAS::logout(['service' => $service]);
        } else {
            phpCAS::logout();
        }
    }

    private function setupCasClient()
    {
        phpCAS::client(CAS_VERSION_2_0, $this->casConfig['host'], $this->casConfig['port'], $this->casConfig['context']);
        phpCAS::setCasServerCACert(CaBundle::getSystemCaRootBundlePath());
    }
}

<?php
// src/Security/LoginFormAuthenticator.php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\Api\GLPIApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Provider\LdapBindAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security.login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $ldap;
    private $params;
    private $glpiApi;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, Ldap $ldap, ParameterBagInterface $params,GLPIApi $glpiApi)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->ldap = $ldap;
        $this->params = $params;
        $this->glpiApi = $glpiApi;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {

        if (null == $request->request->get('_password')) {
            throw new BadCredentialsException('The presented credentials are invalid.');
        }
        $credentials = [
            'email' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }


    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $ldapUser = $this->getLdapUser($credentials);

        if ($ldapUser)
        {
            // Check if user is present in database
            $userIsInDb = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
            if (!$userIsInDb) {

                // Add LDAP to database
                $glpiId = $this->glpiApi->getUserId($credentials["email"]);
                dump($glpiId);
                $ldapDisplayName = $ldapUser->getAttributes()['displayName'][0];
                $insertUser = new User();
                $insertUser->setEmail($credentials["email"]);
                $insertUser->setLdapAuth(1);
                $insertUser->setFullName($ldapDisplayName);
                if ($glpiId) {
                    $insertUser->setGlpiId($glpiId);
                }

                $this->entityManager->persist($insertUser);
                $this->entityManager->flush();
                
            }
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        return $user;
    }


    /**
     * Get parameters from services.yaml
     */
    public function getLdapParams() 
    {
        return $this->params->get('ldap');
    }

    /**
     * Search user in ldap
     *  @param Array $credentials
     */
    public function getLdapUser($credentials) 
    {
        $ldapParams = $this->getLdapParams();
        
        $filter = $ldapParams['filter'];
        $uid_key = $ldapParams['uid_key'];
        $queryFilter = '(&'.$filter.'('.$uid_key.'='.$credentials['email'].'))';

        $this->ldap->bind($ldapParams["search_dn"], $ldapParams["search_password"]);
        $query = $this->ldap->query($ldapParams["base_dn"], $queryFilter);
        $results = $query->execute();

        if ($results->count() > 0)
        {
            foreach ($results as $entry) 
            {
                $user = $entry;
            }
        } else {
            return false;
        }

        return $user;
    }


    /**
     * Try to connect to LDAP with user credentials
     * @param Array $credentials
     */
    public function checkLdapCredentials($credentials)
    {
        $ldapDn = $this->getLdapUser($credentials);

        if ($ldapDn) 
        {
            $ldapDn = $ldapDn->getAttributes()['distinguishedName'][0];
        }

        try {
            // try to bind with the username and provided password
            $this->ldap->bind($ldapDn, $credentials["password"]);

        } catch (\Symfony\Component\Ldap\Exception\ConnectionException $e) {
            throw new BadCredentialsException('The presented credentials are invalid.');
        }

        return true;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {

        $checkLocalPassword = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);

        if (!$checkLocalPassword) 
        {
           return $this->checkLdapCredentials($credentials);
        }

        return true;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        error_log("onAuthenticationSuccess");
        return new RedirectResponse($this->urlGenerator->generate('home'));
        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}

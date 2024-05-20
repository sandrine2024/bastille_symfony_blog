<?php
namespace App\Controller\Visitor\Registration;



use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'visitor_registration_register', methods: ['GET','POST'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager
        ): Response
    {

        // Si l'utilisateur est déjà connecté,
        // il n'a plus rien à faire sur la page de connexion.
            // Redirigeons-le vers la page d'accueil
            if ($this->getUser()) {
                return $this->redirectToRoute('visitor_welcome_index');
            }

        // 1-Créons l'utilisateur à insérer en base de données
        $user = new User();

        // 2- Créons le formulaire d'inscription
        $form = $this->createForm(RegistrationFormType::class, $user);

        // 4- Associons au formulaire, les données de la requête
        $form->handleRequest($request);

        // 5- Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // 6- Encodons le mot de passe
            $passwordHashed= $userPasswordHasher->hashPassword($user,$form->get('password')->getData());

             // 7- Mettons à jour le mot de passe de l'utilisateur
            $user->setPassword($passwordHashed);
            
            // 8- Demandons au manager des entités de préparer la requête d'insertion de l'utilisateur qui s'insscrit en base de données
            $entityManager->persist($user);

            // 9- Executons la requête
            $entityManager->flush();

            // 10- Envoyons l'email de vérification du compte à l'utilisateur
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('medecine_du_monde@gmail.com', 'Julie Dupont'))
                    ->to($user->getEmail())
                    ->subject('Vérifications de votre compte sur le blog de Julis Dupont')
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            // 11- Rediriger l'utilisateur vers la page d'accueil
            return $this->redirectToRoute('visitor_registration_waiting_for_email_verification');
        }
        // 3- Passons le formulaire à la page ^pour affichage
        return $this->render('pages/visitor/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Cette annotation définit une nouvelle route pour l'application.
// '/register/waiting-for-email-verification' est l'URI qui sera utilisé dans le navigateur.
// 'visitor_registration_waiting_for_email_verification' est le nom de la route, qui peut être utilisé pour générer l'URL dans les templates ou les redirections.
// 'methods: ['GET']' indique que cette route répond uniquement aux requêtes HTTP de type GET.
#[Route('/register/waiting-for-email-verification', name:'visitor_registration_waiting_for_email_verification', methods: ['GET'])]

// Cette fonction est appelée lorsque la route correspondante est demandée par un navigateur.
public function waitingForEmailVerification():Response
{
    // Cette ligne utilise le service 'render' pour créer une réponse HTTP.
    // Elle charge le template Twig spécifié et le renvoie comme réponse au navigateur.
    // 'pages/visitor/registration/waiting_for_email_verification.html.twig' est le chemin vers le template qui sera rendu.
    return $this->render('pages/visitor/registration/waiting_for_email_verification.html.twig');
}


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('visitor_registration_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Modifiez la redirection en cas de succès et gérez ou supprimez le message flash dans vos modèles
        $this->addFlash('success', 'Votre compte a été vérifié, vous pouvez vous connecter.');

        return $this->redirectToRoute('visitor_authentication_login');
    }
}

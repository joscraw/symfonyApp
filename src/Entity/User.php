<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use App\Traits\Timestampable;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"INITIAL_REGISTRATION"})
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"adminUser" = "AdminUser", "user" = "User"})
 */
class User implements UserInterface
{
    use Timestampable;

    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN_USER = 'ROLE_ADMIN_USER';

    /**
     * These are roles that the user can select when signing up. Don't actually
     * affect what the user can see/do on the app
     *
     * @var array
     */
    public static $roleOptions = [
        'Doctor' => 'DOCTOR',
        'Medical Professional' => 'MEDICAL_PROFESSIONAL',
        'Medical Assistant' => 'MEDICAL_ASSISTANT',
        'Clinic Admin' => 'CLINIC_ADMIN',
        'Patient' => 'PATIENT',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="Don't forget an email for your user!", groups={"INITIAL_REGISTRATION"})
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * @Assert\NotBlank(message="Don't forget a password for your user!")
     */
    protected $plainPassword;

    /**
     * InvitationCode
     *
     * The invitation code associated with a user.
     *
     * @var string
     *
     * @ORM\Column(name="invitation_code", type="string", length=255, nullable=true)
     */
    protected $invitationCode;

    /**
     * @Assert\NotBlank(message="Don't forget a first name for your user!", groups={"INITIAL_REGISTRATION"})
     *
     * @ORM\Column(type="string", length=24)
     */
    protected $firstName;

    /**
     * @Assert\NotBlank(message="Don't forget a last name for your user!", groups={"INITIAL_REGISTRATION"})
     *
     * @ORM\Column(type="string", length=24)
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $passwordResetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordResetTokenTimestamp;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @ORM\Column(type="boolean")
     */
    protected $activated = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ApiToken", mappedBy="user", orphanRemoval=true)
     */
    private $apiTokens;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $activationCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $photo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $agreedToTermsAt;

    public function __construct()
    {
        $this->apiTokens = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    /**
     * @param string $passwordResetToken
     * @return User
     * @throws \Exception
     */
    public function setPasswordResetToken($passwordResetToken = null)
    {
        if (empty($passwordResetToken)) {
            $passwordResetToken = bin2hex(random_bytes(32));
        }

        if (strlen($passwordResetToken) !== 64) {
            throw new \InvalidArgumentException('Reset token must be 64 characters in length');
        }

        $this->passwordResetToken = $passwordResetToken;

        $this->setPasswordResetTokenTimestamp();

        return $this;
    }

    public function getPasswordResetTokenTimestamp(): ?\DateTimeInterface
    {
        return $this->passwordResetTokenTimestamp;
    }

    /**
     * @param DateTime $passwordResetTokenTimestamp
     * @return User
     * @throws \Exception
     */
    public function setPasswordResetTokenTimestamp(DateTime $passwordResetTokenTimestamp = null)
    {
        if (empty($passwordResetTokenTimestamp)) {
            $passwordResetTokenTimestamp = new DateTime();
        }

        $this->passwordResetTokenTimestamp = $passwordResetTokenTimestamp;

        return $this;
    }

    /**
     * Clear out password reset token related fields
     *
     * @return User
     */
    public function clearPasswordResetToken()
    {
        $this->passwordResetToken          = null;
        $this->passwordResetTokenTimestamp = null;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
     /*   // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';*/

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * @return Collection|ApiToken[]
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens[] = $apiToken;
            $apiToken->setUser($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self
    {
        if ($this->apiTokens->contains($apiToken)) {
            $this->apiTokens->removeElement($apiToken);
            // set the owning side to null (unless already changed)
            if ($apiToken->getUser() === $this) {
                $apiToken->setUser(null);
            }
        }

        return $this;
    }

    public function getAgreedToTermsAt()
    {
        return $this->agreedToTermsAt;
    }

    public function agreeToTerms()
    {
        $this->agreedToTermsAt = new \DateTime();
    }

    public function setAgreedToTermsAt($agreedToTermsAt)
    {
        $this->agreedToTermsAt = $agreedToTermsAt;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    public function getActivationCode()
    {
        return $this->activationCode;
    }

    public function setActivationCode($activationCode)
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    public function initializeNewUser($activationCode = true, $invitationCode = false)
    {
        if($activationCode) {
            $activationCode = bin2hex(random_bytes(32));
            $this->setActivationCode($activationCode);
        }

        if($invitationCode) {
            $invitationCode = bin2hex(random_bytes(32));
            $this->setInvitationCode($invitationCode);
        }

        $this->roles[] = self::ROLE_USER;
    }

    /**
     * @return string
     */
    public function getInvitationCode()
    {
        return $this->invitationCode;
    }

    /**
     * @param string $invitationCode
     */
    public function setInvitationCode($invitationCode)
    {
        $this->invitationCode = $invitationCode;
    }

    public function getPhotoPath()
    {
        return UploaderHelper::CLINIC_LOGO.'/'.$this->getPhoto();
    }
}

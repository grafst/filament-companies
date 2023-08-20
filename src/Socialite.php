<?php

namespace Wallo\FilamentCompanies;

use App\Models\ConnectedAccount;
use Closure;
use Wallo\FilamentCompanies\Contracts\CreatesConnectedAccounts;
use Wallo\FilamentCompanies\Contracts\CreatesUserFromProvider;
use Wallo\FilamentCompanies\Contracts\GeneratesProviderRedirect;
use Wallo\FilamentCompanies\Contracts\HandlesInvalidState;
use Wallo\FilamentCompanies\Contracts\ResolvesSocialiteUsers;
use Wallo\FilamentCompanies\Contracts\SetsUserPasswords;
use Wallo\FilamentCompanies\Contracts\UpdatesConnectedAccounts;

class Socialite
{
    /**
     * The user model that should be used by FilamentCompanies.
     */
    public static string $connectedAccountModel = ConnectedAccount::class;

    /**
     * Determine if the application is using any socialite features.
     */
    public static bool $hasSocialiteFeatures = false;

    /**
     * The socialite providers that should be used by Company.
     */
    public static array $supportedSocialiteProviders = [
        'github' => false,
        'gitlab' => false,
        'google' => false,
        'facebook' => false,
        'linkedin' => false,
        'bitbucket' => false,
        'twitter' => false,
        'twitter-oauth-2' => false,
    ];

    /**
     * The socialite features that should be used by Company.
     */
    public static array $supportedSocialiteFeatures = [
        'rememberSession' => false,
        'refreshOAuthTokens' => false,
        'providerAvatars' => false,
        'generateMissingEmails' => false,
        'loginOnRegistration' => false,
        'createAccountOnFirstLogin' => false,
    ];

    public function enableSocialite(bool|Closure|null $condition = true): static
    {
        static::$hasSocialiteFeatures = $condition instanceof Closure ? $condition() : $condition;

        return $this;
    }

    public function setProviders(array|null $providers = null): static
    {
        if (is_array($providers)) {
            foreach ($providers as $provider) {
                if (array_key_exists($provider, static::$supportedSocialiteProviders)) {
                    static::$supportedSocialiteProviders[$provider] = true;
                }
            }
        }

        return $this;
    }

    public function setFeatures(array|null $features = null): static
    {
        if (is_array($features)) {
            foreach ($features as $feature) {
                if (array_key_exists($feature, static::$supportedSocialiteFeatures)) {
                    static::$supportedSocialiteFeatures[$feature] = true;
                }
            }
        }

        return $this;
    }

    /**
     * Determine if the application has support for socialite.
     */
    public static function hasSocialiteFeatures(): bool
    {
        return static::$hasSocialiteFeatures;
    }

    /**
     * Determine if the application has support for the Bitbucket provider.
     */
    public static function hasBitbucket(): bool
    {
        return static::$supportedSocialiteProviders['bitbucket'];
    }

    /**
     * Determine if the application has support for the Facebook provider.
     */
    public static function hasFacebook(): bool
    {
        return static::$supportedSocialiteProviders['facebook'];
    }

    /**
     * Determine if the application has support for the GitLab provider.
     */
    public static function hasGitlab(): bool
    {
        return static::$supportedSocialiteProviders['gitlab'];
    }

    /**
     * Determine if the application has support for the GitHub provider.
     */
    public static function hasGithub(): bool
    {
        return static::$supportedSocialiteProviders['github'];
    }

    /**
     * Determine if the application has support for the Google provider.
     */
    public static function hasGoogle(): bool
    {
        return static::$supportedSocialiteProviders['google'];
    }

    /**
     * Determine if the application has support for the LinkedIn provider.
     */
    public static function hasLinkedIn(): bool
    {
        return static::$supportedSocialiteProviders['linkedin'];
    }

    /**
     * Determine if the application has support for the Twitter provider.
     */
    public static function hasTwitter(): bool
    {
        return static::$supportedSocialiteProviders['twitter'];
    }

    /**
     * Determine if the application has support for the Twitter OAuth 2.0 provider.
     */
    public static function hasTwitterOAuth2(): bool
    {
        return static::$supportedSocialiteProviders['twitter-oauth-2'];
    }

    /**
     * Determine if the application has support for Remembering Sessions.
     */
    public static function hasRememberSessionFeature(): bool
    {
        return static::$supportedSocialiteFeatures['rememberSession'];
    }

    /**
     * Determine if the application has support for Refreshing OAuth Tokens.
     */
    public static function hasRefreshOAuthTokensFeature(): bool
    {
        return static::$supportedSocialiteFeatures['refreshOAuthTokens'];
    }

    /**
     * Determine if the application has support for Provider Avatars.
     */
    public static function hasProviderAvatarsFeature(): bool
    {
        return static::$supportedSocialiteFeatures['providerAvatars'];
    }

    /**
     * Determine if the application has support for Generating Missing Emails.
     */
    public static function generatesMissingEmails(): bool
    {
        return static::$supportedSocialiteFeatures['generateMissingEmails'];
    }

    /**
     * Determine if the application has support for Logging in on Registration.
     */
    public static function hasLoginOnRegistrationFeature(): bool
    {
        return static::$supportedSocialiteFeatures['loginOnRegistration'];
    }

    /**
     * Determine if the application has support for Creating Accounts on First Login.
     */
    public static function hasCreateAccountOnFirstLoginFeature(): bool
    {
        return static::$supportedSocialiteFeatures['createAccountOnFirstLogin'];
    }

    /**
     * Get all of the socialite providers and whether the application supports them.
     */
    public static function providers(): array
    {
        return static::$supportedSocialiteProviders;
    }

    /**
     * Find a connected account instance for a given provider and provider ID.
     */
    public static function findConnectedAccountForProviderAndId(string $provider, string $providerId): mixed
    {
        return static::newConnectedAccountModel()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * Get the name of the connected account model used by the application.
     */
    public static function connectedAccountModel(): string
    {
        return static::$connectedAccountModel;
    }

    /**
     * Get a new instance of the connected account model.
     */
    public static function newConnectedAccountModel(): mixed
    {
        $model = static::connectedAccountModel();

        return new $model;
    }

    /**
     * Specify the connected account model that should be used by FilamentCompanies.
     */
    public static function useConnectedAccountModel(string $model): static
    {
        static::$connectedAccountModel = $model;

        return new static;
    }

    /**
     * Register a class / callback that should be used to resolve the user for a Socialite Provider.
     */
    public static function resolvesSocialiteUsersUsing(string $class): void
    {
        app()->singleton(ResolvesSocialiteUsers::class, $class);
    }

    /**
     * Register a class / callback that should be used to create users from social providers.
     */
    public static function createUsersFromProviderUsing(string $class): void
    {
        app()->singleton(CreatesUserFromProvider::class, $class);
    }

    /**
     * Register a class / callback that should be used to create connected accounts.
     */
    public static function createConnectedAccountsUsing(string $class): void
    {
        app()->singleton(CreatesConnectedAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to update connected accounts.
     */
    public static function updateConnectedAccountsUsing(string $class): void
    {
        app()->singleton(UpdatesConnectedAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to set user passwords.
     */
    public static function setUserPasswordsUsing(callable|string $callback): void
    {
        app()->singleton(SetsUserPasswords::class, $callback);
    }

    /**
     * Register a class / callback that should be used to set user passwords.
     */
    public static function handlesInvalidStateUsing(callable|string $callback): void
    {
        app()->singleton(HandlesInvalidState::class, $callback);
    }

    /**
     * Register a class / callback that should be used for generating provider redirects.
     */
    public static function generatesProvidersRedirectsUsing(callable|string $callback): void
    {
        app()->singleton(GeneratesProviderRedirect::class, $callback);
    }
}

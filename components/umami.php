<?php
declare(strict_types=1);

/**
 * Build the shared, environment-driven Umami configuration for every public
 * page served by this project.
 *
 * @return array{
 *     enabled: bool,
 *     scriptUrl: string,
 *     websiteId: string,
 *     origin: string,
 *     domains: string
 * }
 */
function umamiConfiguration(string $siteUrl): array
{
    $scriptUrl = trim((string) (getenv('UMAMI_SCRIPT_URL') ?: ''));
    $websiteId = trim((string) (getenv('UMAMI_WEBSITE_ID') ?: ''));
    $urlParts = $scriptUrl !== '' ? parse_url($scriptUrl) : false;
    $enabled =
        is_array($urlParts) &&
        strtolower((string) ($urlParts['scheme'] ?? '')) === 'https' &&
        isset($urlParts['host']) &&
        !isset($urlParts['user']) &&
        !isset($urlParts['pass']) &&
        filter_var($scriptUrl, FILTER_VALIDATE_URL) !== false &&
        preg_match('/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/i', $websiteId) === 1;

    $origin = '';
    if ($enabled) {
        $origin = 'https://' . strtolower((string) $urlParts['host']);
        if (isset($urlParts['port'])) {
            $origin .= ':' . (int) $urlParts['port'];
        }
    }

    $siteHost = strtolower((string) (parse_url($siteUrl, PHP_URL_HOST) ?: 'sergiotech.es'));
    $domainCandidates = preg_split(
        '/\s*,\s*/',
        trim((string) (getenv('UMAMI_DOMAINS') ?: $siteHost)),
        -1,
        PREG_SPLIT_NO_EMPTY
    ) ?: [];
    $domains = [];
    foreach ($domainCandidates as $domainCandidate) {
        $domainCandidate = strtolower(trim($domainCandidate));
        if (filter_var($domainCandidate, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false) {
            $domains[] = $domainCandidate;
        }
    }

    return [
        'enabled' => $enabled,
        'scriptUrl' => $scriptUrl,
        'websiteId' => $websiteId,
        'origin' => $origin,
        'domains' => implode(',', array_values(array_unique($domains ?: [$siteHost]))),
    ];
}

/**
 * @param array{enabled: bool, scriptUrl: string, websiteId: string, origin: string, domains: string} $config
 */
function umamiRenderTrackingScript(array $config, string $tag): void
{
    if (!$config['enabled']) {
        return;
    }

    $escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    ?>
<script
    defer
    src="<?= $escape($config['scriptUrl']) ?>"
    data-website-id="<?= $escape($config['websiteId']) ?>"
    data-domains="<?= $escape($config['domains']) ?>"
    data-do-not-track="true"
    data-exclude-search="true"
    data-tag="<?= $escape($tag) ?>"></script>
    <?php
}

<?php
declare(strict_types=1);

use OCP\Util;
use OCP\IL10N;

$appId = OCA\VulnDexBeacon\AppInfo\Application::APP_ID;

\OCP\Util::addStyle($appId, 'vulndex-admin');

/**
 * @var OCP\IL10N $l
 * @var array $_
 */
?>

<div class="section">
    <h2>VulnDex Beacon</h2>
    <p><?php echo $l->t('Der VulnDex Beacon für Nextcloud übermittelt die aktuelle Nextcloud-Version, technische Informationen und installierte App-Versionen an VulnDex. In den VulnDex Docs &raquo; %1$s befinden sich weitere Informationen.',
                        ['<a href="https://vulndex.at/docs/10-vulndex-docs/137-beacon-fur-nextcloud" target="_blank">Beacon für Nextcloud</a>']); ?></p>
</div>

<div class="section">
    <h2><?php p($l->t('API-Schlüssel')) ?></h2>

    <p><?php p($l->t('Für die Integration benötigen Sie einen API-Schlüssel. Diesen erhalten Sie über die VulnDex-Plattform.')) ?></p>

    <form id="vulndexbeacon-form">
        <p>
            <label for="apiKey"><?php p($l->t('API-Schlüssel')) ?></label><br>
            <input type="password" disabled id="vulndexbeacon-apiKey" name="apiKey" style="width: 100%;" value="" data-set="<?php p($_['apiKeySet'] ? 'yes' : 'no'); ?>" placeholder="<?php p($_['apiKeySet'] ? $l->t('✓ API-Schlüssel ist gesetzt') : $l->t('API-Schlüssel eingeben')); ?>">
        </p>

        <p>
            <button type="button" disabled id="vulndexbeacon-save" class="button primary"><?php p($l->t('Speichern')) ?></button> <span id="savebtn_loader" class="icon-loading-small hidden" aria-hidden="true"></span>
        <p id="vulndexbeacon-statusSave"></p>
        </p>


    </form>

</div>

<div class="section">
    <h2><?php p($l->t('Aktueller Status')) ?></h2>

    <?php if ($_['lastSend']['timestamp'] !== null){ ?>
        <p>
            <strong><?php p($l->t('Letzte Meldung')) ?>:</strong> <span id="vulndex-lastsend"><?php p(date('d.m.Y H:i:s', $_['lastSend']['timestamp'])); ?></span><br>
            <strong><?php p($l->t('Status')) ?>:</strong>
            <span  id="vulndex-laststatuscolor" style="color: <?php p($_['lastSend']['success'] ? 'green' : 'red'); ?>;">
                <span id="vulndex-laststatus"><?php p($_['lastSend']['success'] ?  $l->t('✓ Erfolgreich') : $l->t('✗ Fehler')); ?></span>
			</span><br>
            <strong><?php p($l->t('Server Rückmeldung')) ?>:</strong> <span id="vulndex-lastresponse"><?php p($_['lastSend']['message']); ?></span>
        </p>
    <?php } else { ?>
        <p style="color: #999;">
            <?php p($_['lastSend']['message']); ?>
        </p>
    <?php } ?>
    <div>
        <p><button type="button"  disabled id="vulndexbeacon-send" class="button"><?php p($l->t('Jetzt senden')) ?></button>  <span id="sendbtn_loader" class="icon-loading-small hidden" aria-hidden="true"></span></p>
        <p id="vulndexbeacon-statusSend"></p>
    </div>
</div>

<?php
Util::addScript($appId, $appId . '-adminSettings');

?>

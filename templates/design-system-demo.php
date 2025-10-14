<?php
/**
 * Design System Demo Template
 * Access via ?design-system-demo=1 (logged-in only).
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;

get_header();
?>

<main class="ds-demo bg-tertiary">
    <div class="ds-demo__wrapper" style="max-width: 1200px; margin: 0 auto; padding: var(--space-8) var(--space-6);">
        <section class="card card-hover mb-6">
            <div class="card-body">
                <h1 class="text-3xl text-primary mb-3">Design System Demo</h1>
                <p class="text-base text-tertiary mb-3">Questa pagina raccoglie i componenti principali del design system per verificare rapidamente font, colori, spacing e interazioni.</p>
                <p class="text-sm text-secondary">URL rapido: <code>?design-system-demo=1</code> (visibile solo agli utenti autenticati). Rimuovi il file <code>includes/design-system-demo.php</code> e questa template quando non serve più.</p>
            </div>
        </section>

        <section class="mb-6">
            <h2 class="text-2xl text-primary mb-4">Tipografia</h2>
            <div class="card">
                <div class="card-body">
                    <p class="text-3xl text-primary mb-2">Titolo H1 Demo</p>
                    <p class="text-2xl text-secondary mb-2">Titolo H2 Demo</p>
                    <p class="text-xl text-tertiary mb-2">Titolo H3 Demo</p>
                    <p class="text-base text-primary mb-2">Paragrafo base con testo principale per verificare leggibilità e line-height.</p>
                    <p class="text-sm text-secondary mb-1">Testo secondario per metadati o note.</p>
                    <p class="text-xs text-muted">Legenda / helper text.</p>
                </div>
            </div>
        </section>

        <section class="mb-6">
            <h2 class="text-2xl text-primary mb-4">Buttons</h2>
            <div class="card">
                <div class="card-body">
                    <div class="grid grid-cols-1 grid-cols-md-2 grid-cols-lg-3 gap-4">
                        <div class="p-3 bg-secondary rounded">
                            <p class="text-sm text-secondary mb-2 uppercase">Primari</p>
                            <div class="flex" style="gap: var(--space-2); flex-wrap: wrap;">
                                <button class="btn btn-primary">Primary</button>
                                <button class="btn btn-secondary">Secondary</button>
                                <button class="btn btn-outline">Outline</button>
                                <button class="btn btn-ghost">Ghost</button>
                                <button class="btn btn-link">Link</button>
                            </div>
                        </div>
                        <div class="p-3 bg-secondary rounded">
                            <p class="text-sm text-secondary mb-2 uppercase">Stati</p>
                            <div class="flex" style="gap: var(--space-2); flex-wrap: wrap;">
                                <button class="btn btn-success">Successo</button>
                                <button class="btn btn-warning">Warning</button>
                                <button class="btn btn-error">Errore</button>
                                <button class="btn btn-primary" disabled>Disabilitato</button>
                            </div>
                        </div>
                        <div class="p-3 bg-secondary rounded">
                            <p class="text-sm text-secondary mb-2 uppercase">Dimensioni</p>
                            <div class="flex" style="gap: var(--space-2); flex-wrap: wrap; align-items: center;">
                                <button class="btn btn-primary btn-lg">Large</button>
                                <button class="btn btn-primary">Default</button>
                                <button class="btn btn-primary btn-sm">Small</button>
                                <button class="btn btn-primary btn-icon" aria-label="Icon button">
                                    <span class="badge-dot bg-white"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-6">
            <h2 class="text-2xl text-primary mb-4">Cards</h2>
            <div class="grid grid-cols-1 grid-cols-md-2 gap-4">
                <div class="card card-hover">
                    <div class="card-header">Card standard</div>
                    <div class="card-body">
                        <p class="text-base text-tertiary mb-3">Card con header, body e footer per contenuti generici.</p>
                        <ul class="text-sm text-secondary" style="list-style: disc; padding-left: var(--space-5);">
                            <li>Typography system</li>
                            <li>Spacing utilities</li>
                            <li>Hover/Focus states</li>
                        </ul>
                    </div>
                    <div class="card-footer">Aggiornato il <?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></div>
                </div>
                <div class="card card-clickable">
                    <div class="card-body">
                        <p class="badge badge-primary badge-pill mb-3">Nuovo</p>
                        <h3 class="text-xl text-primary mb-2">Card Interattiva</h3>
                        <p class="text-base text-tertiary mb-4">Usa questa variante per elementi cliccabili come documenti o corsi.</p>
                        <a class="btn btn-outline" href="#">Vai al dettaglio</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-6">
            <h2 class="text-2xl text-primary mb-4">Badges &amp; Stati</h2>
            <div class="card">
                <div class="card-body">
                    <div class="flex" style="gap: var(--space-2); flex-wrap: wrap;">
                        <span class="badge badge-primary">Primary</span>
                        <span class="badge badge-secondary">Secondary</span>
                        <span class="badge badge-success">Success</span>
                        <span class="badge badge-warning">Warning</span>
                        <span class="badge badge-error">Error</span>
                        <span class="badge badge-info">Info</span>
                        <span class="badge badge-outline-primary">Outline</span>
                        <span class="badge badge-pill badge-success">Pill</span>
                        <span class="badge badge-status-active">Attivo</span>
                        <span class="badge badge-status-pending">In attesa</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-6">
            <h2 class="text-2xl text-primary mb-4">Form Controls</h2>
            <div class="card">
                <div class="card-body">
                    <div class="grid grid-cols-1 grid-cols-md-2 gap-4">
                        <div>
                            <div class="input-group">
                                <label for="demo-name">Nome <span class="required">*</span></label>
                                <input id="demo-name" type="text" class="input-field" placeholder="Mario Rossi">
                                <span class="input-helper">Inserisci il nome completo.</span>
                            </div>
                            <div class="input-group">
                                <label for="demo-email">Email</label>
                                <input id="demo-email" type="email" class="input-field error" placeholder="nome@dominio.it">
                                <span class="input-error">Formato email non valido</span>
                            </div>
                            <div class="input-group">
                                <label for="demo-select">Unità di offerta</label>
                                <select id="demo-select" class="select-field">
                                    <option>RSA Meridiana</option>
                                    <option>CDI Respiro</option>
                                    <option>Centro Diurno Disabili</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <div class="input-group">
                                <label for="demo-textarea">Note</label>
                                <textarea id="demo-textarea" class="textarea" placeholder="Inserisci eventuali note aggiuntive"></textarea>
                            </div>
                            <div class="input-group">
                                <label class="toggle">
                                    <input type="checkbox" class="toggle-input" checked>
                                    <span class="toggle-track"></span>
                                    <span class="toggle-label">Accesso ATS abilitato</span>
                                </label>
                            </div>
                            <div class="input-group">
                                <label class="checkbox">
                                    <input type="checkbox" class="checkbox-input" checked>
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Ho letto e accetto</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions mt-4">
                        <button class="btn btn-primary">Salva</button>
                        <button class="btn btn-secondary">Annulla</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-6">
            <h2 class="text-2xl text-primary mb-4">Table &amp; Scroll</h2>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Categoria</th>
                                    <th>Ultimo aggiornamento</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Protocollo Emergenze</td>
                                    <td>Procedure</td>
                                    <td>12/09/2025</td>
                                    <td>
                                        <span class="table-status">
                                            <span class="status-dot status-active"></span>
                                            <span class="text-sm">Attivo</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Modulo Formazione</td>
                                    <td>Formazione</td>
                                    <td>30/08/2025</td>
                                    <td>
                                        <span class="table-status">
                                            <span class="status-dot status-pending"></span>
                                            <span class="text-sm">In revisione</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Convenzione Welfare</td>
                                    <td>Convenzioni</td>
                                    <td>15/07/2025</td>
                                    <td>
                                        <span class="table-status">
                                            <span class="status-dot status-inactive"></span>
                                            <span class="text-sm">Sospesa</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Checklist Sicurezza</td>
                                    <td>Sicurezza</td>
                                    <td>05/06/2025</td>
                                    <td>
                                        <span class="table-status">
                                            <span class="status-dot status-error"></span>
                                            <span class="text-sm">Errore</span>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-6">
            <h2 class="text-2xl text-primary mb-4">Utilities &amp; Layout</h2>
            <div class="card">
                <div class="card-body">
                    <div class="grid grid-cols-1 grid-cols-md-3 gap-4">
                        <div class="bg-primary text-white p-4 rounded-lg">
                            <p class="text-sm uppercase mb-2">Spacing</p>
                            <p class="text-base">Classi <code>p-*</code>, <code>m-*</code>, <code>mt-*</code> per padding e margini.</p>
                        </div>
                        <div class="bg-secondary text-primary p-4 rounded-lg">
                            <p class="text-sm uppercase mb-2">Grid</p>
                            <p class="text-base">Classi responsive <code>grid-cols-*</code> e breakpoint <code>grid-cols-md-*</code>.</p>
                        </div>
                        <div class="bg-primary text-white p-4 rounded-lg">
                            <p class="text-sm uppercase mb-2">Tipologia</p>
                            <p class="text-base">Utility testo come <code>text-muted</code>, <code>text-success</code>, <code>uppercase</code>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
get_footer();

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Template submission : Customized Template</title>
     <style>
     /* ---- Design tokens (hook into your color system as needed) ---- */
     :root {
          --surface: #ffffff;
          --ink-600: #1f2937;
          --ink-400: #6b7280;
          --brand: var(--brand, #2563eb);
          /* falls back if not defined globally */
          --brand-600: var(--brand-600, #1e40af);
          --ring: rgba(37, 99, 235, .35);
          --shadow: 0 6px 20px rgba(0, 0, 0, .08);
          --radius: 16px;
     }

     /* ---- Template Card ---- */
     .template-card {
          display: block;
          text-decoration: none;
          color: inherit;
          background: var(--surface);
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          transition: transform .18s ease, box-shadow .18s ease;
          position: relative;
          overflow: hidden;
          outline: none;
     }

     .template-card:focus-visible {
          box-shadow: var(--shadow), 0 0 0 6px var(--ring);
          transform: translateY(-2px);
     }

     .template-card:hover {
          transform: translateY(-4px);
          box-shadow: 0 12px 32px rgba(0, 0, 0, .12);
     }

     .template-thumb {
          background: #0b1220;
     }

     .template-thumb>img {
          width: 100%;
          height: 400px;
          object-fit: cover;
          object-position: top;
          display: block;
     }

     /* Placeholder shown when a template has no thumbnail uploaded. */
     .template-thumb-empty {
          height: 400px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #64748b;
          font-size: 2.5rem;
     }

     .template-meta {
          padding: .9rem 1rem 1.1rem;
          text-align: center;
     }

     .template-title {
          display: block;
          font-weight: 700;
          color: var(--ink-600);
          line-height: 1.15;
     }

     .template-category {
          display: block;
          margin-top: .25rem;
          font-size: .8rem;
          color: var(--ink-400);
     }

     /* ---- Action buttons ---- */
     .template-item {
          position: relative;
     }

     .template-actions {
          position: absolute;
          top: .75rem;
          right: 1.25rem;
          z-index: 2;
          display: flex;
          gap: .35rem;
     }

     .template-actions .btn {
          --bs-btn-padding-y: .2rem;
          --bs-btn-padding-x: .45rem;
          box-shadow: 0 2px 8px rgba(0, 0, 0, .25);
     }

     /* A soft-deleted template stays listed but is visually muted. */
     .template-item.is-inactive .template-card {
          opacity: .45;
          filter: grayscale(1);
     }

     /* spacing helper for larger screens */
     @media (min-width: 992px) {
          .py-lg-6 {
               padding-top: 4.5rem !important;
               padding-bottom: 4.5rem !important;
          }
     }
     </style>
</head>
<?php
require_once __DIR__ . '/../models/templateCatalogue.php';

// Catalogue of uploaded templates, stored in data/templates.json.
$allTemplates = tplReadCatalogue();

// Filter buttons. Only categories that actually have templates are rendered.
$templateCategories = tplCategories();

$activeTemplates = array_values(array_filter($allTemplates, function ($row) {
     return !empty($row['active']);
}));
$usedCategories = [];
foreach ($activeTemplates as $row) {
     if (isset($row['category'])) {
          $usedCategories[$row['category']] = true;
     }
}
$inactiveCount = count($allTemplates) - count($activeTemplates);

// Thumbnails can only be rendered when a headless browser is installed.
$canAutoThumbnail = tplCanAutoThumbnail();

/*
 * A row can name a thumbnail that is not on this server, for example when the
 * catalogue was deployed but the image files were not. Those cards show the
 * placeholder, so count them separately from rows that never had one.
 */
$missingThumbCount = 0;
foreach ($activeTemplates as $row) {
     $thumb = isset($row['thumbnail']) ? $row['thumbnail'] : '';
     // Match the card's fallback to the conventional file name.
     if ($thumb === '' && isset($row['id'])) {
          $thumb = 'thumbnail' . $row['id'] . ' (Custom).png';
     }
     if ($thumb === '' || !is_file(TPL_THUMB_DIR . '/' . $thumb)) {
          $missingThumbCount++;
     }
}
?>
<div class="row">
     <div class="col">
          <div class="d-flex justify-content-between align-items-center">
               <h4><i class="bi bi-file-richtext"></i> Customized Template</h4>
               <div class="d-flex gap-2">
                    <?php if ($canAutoThumbnail) { ?>
                    <button type="button"
                         class="btn btn-sm <?php echo $missingThumbCount > 0 ? 'btn-warning' : 'btn-outline-secondary'; ?>"
                         id="btnRegenThumbs">
                         <i class="bi bi-arrow-repeat"></i> Regenerate Thumbnails
                         <?php if ($missingThumbCount > 0) { ?>
                         <span class="badge text-bg-light"><?php echo $missingThumbCount; ?> missing</span>
                         <?php } ?>
                    </button>
                    <?php } ?>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnBulkTemplate">
                         <i class="bi bi-files"></i> Bulk Upload
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddTemplate">
                         <i class="bi bi-plus-lg"></i> Add Template
                    </button>
               </div>
          </div>
     </div>
</div>

<div class="d-flex justify-content-center flex-wrap gap-2 pt-3">
     <button type="button" class="btn btn-sm btn-primary template-filter active" data-filter="all">
          All <span class="badge text-bg-light"><?php echo count($activeTemplates); ?></span>
     </button>
     <?php foreach ($templateCategories as $slug => $label) {
          if (!isset($usedCategories[$slug])) {
               continue;
          }
          $count = 0;
          foreach ($activeTemplates as $row) {
               if (isset($row['category']) && $row['category'] === $slug) {
                    $count++;
               }
          }
          ?>
     <button type="button" class="btn btn-sm btn-outline-primary template-filter"
          data-filter="<?php echo htmlspecialchars($slug); ?>">
          <?php echo htmlspecialchars($label); ?> <span class="badge text-bg-light"><?php echo $count; ?></span>
     </button>
     <?php } ?>
     <?php if ($inactiveCount > 0) { ?>
     <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggleRemoved"
          data-shown="0">
          <i class="bi bi-trash"></i> Show removed <span
               class="badge text-bg-light"><?php echo $inactiveCount; ?></span>
     </button>
     <?php } ?>
</div>

<div class="template-hero d-flex justify-content-center py-4">
     <div class="w-100" style="max-width: 1200px;">
          <div id="templates" class="row row-cols-2 row-cols-md-4 g-3 g-lg-4 justify-content-center">
               <?php foreach ($allTemplates as $row) {
                    $tpl = isset($row['id']) ? $row['id'] : '';
                    $category = isset($row['category']) ? $row['category'] : '';
                    $isActive = !empty($row['active']);
                    $categoryLabel = isset($templateCategories[$category]) ? $templateCategories[$category] : ucfirst($category);
                    $file = isset($row['file']) ? $row['file'] : 'Template' . $tpl . '.html';
                    $thumbnail = isset($row['thumbnail']) ? $row['thumbnail'] : null;
                    // The named file may not exist here, so check before linking to it.
                    /*
                     * A row may have no thumbnail recorded, for example when it was
                     * uploaded on a server that cannot render. Fall back to the
                     * conventional file name so an image copied in by hand is still
                     * picked up without editing the catalogue.
                     */
                    if (empty($thumbnail)) {
                         $conventional = 'thumbnail' . $tpl . ' (Custom).png';
                         if (is_file(TPL_THUMB_DIR . '/' . $conventional)) {
                              $thumbnail = $conventional;
                         }
                    }
                    $hasThumbFile = !empty($thumbnail) && is_file(TPL_THUMB_DIR . '/' . $thumbnail);
                    // Templates added before display names existed fall back to the id.
                    $name = isset($row['name']) ? trim((string)$row['name']) : '';
                    $displayName = $name !== '' ? $name : 'Template ' . $tpl;
                    ?>
               <div class="col template-item<?php echo $isActive ? '' : ' is-inactive d-none'; ?>"
                    data-category="<?php echo htmlspecialchars($category); ?>"
                    data-id="<?php echo htmlspecialchars($tpl); ?>"
                    data-name="<?php echo htmlspecialchars($name); ?>"
                    data-active="<?php echo $isActive ? '1' : '0'; ?>"
                    data-hasthumb="<?php echo $hasThumbFile ? '1' : '0'; ?>">
                    <div class="template-actions">
                         <?php if ($isActive) { ?>
                         <?php if ($canAutoThumbnail) { ?>
                         <button type="button"
                              class="btn btn-sm btn-regen-template <?php echo $hasThumbFile ? 'btn-light' : 'btn-warning'; ?>"
                              title="<?php echo $hasThumbFile ? 'Re-render thumbnail for' : 'Thumbnail missing - render it for'; ?> <?php echo htmlspecialchars($displayName); ?>">
                              <i class="bi bi-arrow-repeat"></i>
                         </button>
                         <?php } ?>
                         <button type="button" class="btn btn-light btn-sm btn-edit-template"
                              title="Edit <?php echo htmlspecialchars($displayName); ?>">
                              <i class="bi bi-pencil"></i>
                         </button>
                         <button type="button" class="btn btn-danger btn-sm btn-delete-template"
                              title="Remove <?php echo htmlspecialchars($displayName); ?>">
                              <i class="bi bi-trash"></i>
                         </button>
                         <?php } else { ?>
                         <button type="button" class="btn btn-success btn-sm btn-restore-template"
                              title="Restore <?php echo htmlspecialchars($displayName); ?>">
                              <i class="bi bi-arrow-counterclockwise"></i>
                         </button>
                         <?php } ?>
                    </div>
                    <a href="customized-template/<?php echo rawurlencode($file); ?>" class="template-card"
                         aria-label="Open <?php echo htmlspecialchars($categoryLabel); ?> template <?php echo htmlspecialchars($displayName); ?>">
                         <div class="template-thumb">
                              <?php if ($hasThumbFile) { ?>
                              <img src="../assets/img/custom-template/<?php echo rawurlencode($thumbnail); ?>"
                                   alt="<?php echo htmlspecialchars($displayName); ?> preview" loading="lazy"
                                   decoding="async">
                              <?php } else { ?>
                              <div class="template-thumb-empty"><i class="bi bi-file-earmark-code"></i></div>
                              <?php } ?>
                         </div>
                         <div class="template-meta">
                              <span class="template-title"><?php echo htmlspecialchars($displayName); ?></span>
                              <span class="template-category">
                                   <?php echo htmlspecialchars($categoryLabel); ?>
                                   <?php if ($name !== '') { ?>
                                   &middot; <?php echo htmlspecialchars($tpl); ?>
                                   <?php } ?>
                              </span>
                         </div>
                    </a>
               </div>
               <?php } ?>
          </div>
          <p id="templatesEmpty" class="text-center text-muted py-4 d-none">No templates in this category yet.</p>
     </div>
</div>

<!-- Add / edit modal. One form serves both; mode decides which fields are required. -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
          <form class="modal-content" id="templateForm" enctype="multipart/form-data">
               <div class="modal-header">
                    <h5 class="modal-title" id="templateModalTitle">Add Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                    <input type="hidden" name="mode" id="templateMode" value="add">
                    <div id="templateAlert" class="alert alert-danger d-none" role="alert"></div>

                    <div class="mb-3">
                         <label for="templateId" class="form-label">Template ID</label>
                         <input type="text" class="form-control" id="templateId" name="id" required
                              maxlength="40" pattern="[A-Za-z0-9][A-Za-z0-9._\-]*"
                              placeholder="e.g. 19 or 19.2">
                         <div class="form-text">Letters, numbers, dot, dash and underscore only. Becomes the
                              file name, so it cannot be changed later.</div>
                    </div>

                    <div class="mb-3">
                         <label for="templateName" class="form-label">Display name <span
                                   class="text-muted">(optional)</span></label>
                         <input type="text" class="form-control" id="templateName" name="name"
                              maxlength="80" placeholder="e.g. Serenity Spa">
                         <div class="form-text">Shown on the card. Leave empty to show
                              <code>Template &lt;ID&gt;</code>.</div>
                    </div>

                    <div class="mb-3">
                         <label for="templateCategory" class="form-label">Category</label>
                         <select class="form-select" id="templateCategory" name="category" required>
                              <?php foreach ($templateCategories as $slug => $label) { ?>
                              <option value="<?php echo htmlspecialchars($slug); ?>">
                                   <?php echo htmlspecialchars($label); ?></option>
                              <?php } ?>
                         </select>
                    </div>

                    <div class="mb-3">
                         <label for="templateHtml" class="form-label">HTML file</label>
                         <input type="file" class="form-control" id="templateHtml" name="htmlFile"
                              accept=".html,.htm,text/html" required>
                         <div class="form-text" id="templateHtmlHelp">Only .html or .htm files, up to 5 MB.</div>
                    </div>

                    <div class="mb-3">
                         <label for="templateThumb" class="form-label">Thumbnail <span
                                   class="text-muted">(optional)</span></label>
                         <input type="file" class="form-control" id="templateThumb" name="thumbFile"
                              accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                         <div class="form-text">PNG, JPG or WebP, up to 5 MB.</div>
                    </div>

                    <?php if ($canAutoThumbnail) { ?>
                    <div class="form-check">
                         <input class="form-check-input" type="checkbox" value="1" id="templateAutoThumb"
                              name="autoThumb" checked>
                         <label class="form-check-label" for="templateAutoThumb">
                              Generate the thumbnail from the template automatically
                         </label>
                         <div class="form-text" id="templateAutoThumbHelp">Used only when no thumbnail is
                              uploaded above. Rendering takes a few seconds.</div>
                    </div>
                    <?php } ?>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="templateSubmit">Save</button>
               </div>
          </form>
     </div>
</div>

<!-- Regenerate thumbnails. One request per template, same reason as bulk upload. -->
<div class="modal fade" id="regenModal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
     <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
               <div class="modal-header">
                    <h5 class="modal-title">Regenerate Thumbnails</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                         id="regenClose"></button>
               </div>
               <div class="modal-body">
                    <div id="regenAlert" class="alert alert-danger d-none" role="alert"></div>

                    <div id="regenPickWrap">
                         <p class="mb-2">Render thumbnails again from the template files on this server.</p>
                         <div class="form-check">
                              <input class="form-check-input" type="radio" name="regenScope" id="regenScopeMissing"
                                   value="missing" checked>
                              <label class="form-check-label" for="regenScopeMissing">
                                   Only templates with a missing thumbnail
                                   <span class="badge text-bg-secondary" id="regenMissingCount">0</span>
                              </label>
                         </div>
                         <div class="form-check mb-3">
                              <input class="form-check-input" type="radio" name="regenScope" id="regenScopeAll"
                                   value="all">
                              <label class="form-check-label" for="regenScopeAll">
                                   All visible templates
                                   <span class="badge text-bg-secondary" id="regenAllCount">0</span>
                              </label>
                         </div>
                         <div class="form-text mb-2">Takes roughly 1.5 seconds per template.</div>
                    </div>

                    <div id="regenProgressWrap" class="d-none">
                         <div class="d-flex justify-content-between align-items-center mb-1">
                              <strong id="regenProgressLabel">Rendering...</strong>
                              <span class="text-muted" id="regenProgressCount">0 / 0</span>
                         </div>
                         <div class="progress mb-3" role="progressbar" aria-label="Render progress">
                              <div class="progress-bar progress-bar-striped progress-bar-animated"
                                   id="regenProgressBar" style="width: 0%"></div>
                         </div>
                         <ul class="list-group" id="regenResults"
                              style="max-height: 280px; overflow-y: auto;"></ul>
                    </div>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                         id="regenCancel">Cancel</button>
                    <button type="button" class="btn btn-primary" id="regenStart">Start</button>
               </div>
          </div>
     </div>
</div>

<!-- Bulk upload. Files are posted one at a time so a slow render cannot time out the whole batch. -->
<div class="modal fade" id="bulkModal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
     <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
               <div class="modal-header">
                    <h5 class="modal-title">Bulk Upload Templates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                         id="bulkClose"></button>
               </div>
               <div class="modal-body">
                    <div id="bulkAlert" class="alert alert-danger d-none" role="alert"></div>

                    <div class="mb-3" id="bulkPickWrap">
                         <label for="bulkFiles" class="form-label">HTML files</label>
                         <input type="file" class="form-control" id="bulkFiles" multiple
                              accept=".html,.htm,text/html">
                         <div class="form-text">
                              Select up to <?php echo (int)ini_get('max_file_uploads'); ?> .html or .htm files.
                              The template ID comes from each file name, so
                              <code>Template20.html</code> becomes template <code>20</code>.
                         </div>
                    </div>

                    <div class="mb-3" id="bulkCategoryWrap">
                         <label for="bulkCategory" class="form-label">Category for all files</label>
                         <select class="form-select" id="bulkCategory">
                              <?php foreach ($templateCategories as $slug => $label) { ?>
                              <option value="<?php echo htmlspecialchars($slug); ?>">
                                   <?php echo htmlspecialchars($label); ?></option>
                              <?php } ?>
                         </select>
                    </div>

                    <?php if ($canAutoThumbnail) { ?>
                    <div class="form-check mb-3" id="bulkAutoWrap">
                         <input class="form-check-input" type="checkbox" value="1" id="bulkAutoThumb" checked>
                         <label class="form-check-label" for="bulkAutoThumb">
                              Generate thumbnails automatically
                         </label>
                         <div class="form-text">Adds roughly 1.5 seconds per file.</div>
                    </div>
                    <?php } ?>

                    <div id="bulkProgressWrap" class="d-none">
                         <div class="d-flex justify-content-between align-items-center mb-1">
                              <strong id="bulkProgressLabel">Uploading...</strong>
                              <span class="text-muted" id="bulkProgressCount">0 / 0</span>
                         </div>
                         <div class="progress mb-3" role="progressbar" aria-label="Upload progress">
                              <div class="progress-bar progress-bar-striped progress-bar-animated"
                                   id="bulkProgressBar" style="width: 0%"></div>
                         </div>
                         <ul class="list-group" id="bulkResults"
                              style="max-height: 280px; overflow-y: auto;"></ul>
                    </div>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                         id="bulkCancel">Cancel</button>
                    <button type="button" class="btn btn-primary" id="bulkStart">Upload</button>
               </div>
          </div>
     </div>
</div>

<script>
(function() {
     var buttons = document.querySelectorAll('.template-filter');
     var grid = document.getElementById('templates');
     var items = Array.prototype.slice.call(grid.querySelectorAll('.template-item'));
     var empty = document.getElementById('templatesEmpty');
     var toggleRemoved = document.getElementById('btnToggleRemoved');
     var currentFilter = 'all';

     function showingRemoved() {
          return toggleRemoved !== null && toggleRemoved.dataset.shown === '1';
     }

     function applyFilter() {
          var visible = 0;
          items.forEach(function(item) {
               var isActive = item.dataset.active === '1';
               // Removed templates only appear while the user asks to see them.
               var stateMatch = isActive ? !showingRemoved() : showingRemoved();
               var categoryMatch = (currentFilter === 'all') || (item.dataset.category === currentFilter);
               var match = stateMatch && categoryMatch;
               item.classList.toggle('d-none', !match);
               if (match) {
                    visible++;
               }
          });
          empty.classList.toggle('d-none', visible > 0);
     }

     buttons.forEach(function(button) {
          button.addEventListener('click', function() {
               buttons.forEach(function(other) {
                    other.classList.remove('active', 'btn-primary');
                    other.classList.add('btn-outline-primary');
               });
               button.classList.add('active', 'btn-primary');
               button.classList.remove('btn-outline-primary');
               currentFilter = button.dataset.filter;
               applyFilter();
          });
     });

     if (toggleRemoved !== null) {
          toggleRemoved.addEventListener('click', function() {
               var next = showingRemoved() ? '0' : '1';
               toggleRemoved.dataset.shown = next;
               toggleRemoved.classList.toggle('btn-secondary', next === '1');
               toggleRemoved.classList.toggle('btn-outline-secondary', next === '0');
               toggleRemoved.innerHTML = (next === '1' ? '<i class="bi bi-eye-slash"></i> Hide removed' :
                    '<i class="bi bi-trash"></i> Show removed') +
                    ' <span class="badge text-bg-light"><?php echo $inactiveCount; ?></span>';
               applyFilter();
          });
     }

     // ---- Add / edit / delete ----
     var endpoint = '../models/ajaxCustomTemplate.php';
     var modalEl = document.getElementById('templateModal');
     var modal = new bootstrap.Modal(modalEl);
     var form = document.getElementById('templateForm');
     var alertBox = document.getElementById('templateAlert');
     var submitBtn = document.getElementById('templateSubmit');
     var fieldMode = document.getElementById('templateMode');
     var fieldId = document.getElementById('templateId');
     var fieldCategory = document.getElementById('templateCategory');
     var fieldName = document.getElementById('templateName');
     var fieldHtml = document.getElementById('templateHtml');
     var fieldThumb = document.getElementById('templateThumb');
     var htmlHelp = document.getElementById('templateHtmlHelp');
     var modalTitle = document.getElementById('templateModalTitle');
     var autoThumb = document.getElementById('templateAutoThumb');
     var autoThumbHelp = document.getElementById('templateAutoThumbHelp');

     function showError(message) {
          alertBox.textContent = message;
          alertBox.classList.remove('d-none');
     }

     function clearError() {
          alertBox.textContent = '';
          alertBox.classList.add('d-none');
     }

     document.getElementById('btnAddTemplate').addEventListener('click', function() {
          clearError();
          form.reset();
          fieldMode.value = 'add';
          fieldId.readOnly = false;
          fieldHtml.required = true;
          htmlHelp.textContent = 'Only .html or .htm files, up to 5 MB.';
          if (autoThumb !== null) {
               autoThumb.checked = true;
               autoThumbHelp.textContent = 'Used only when no thumbnail is uploaded above. Rendering takes a few seconds.';
          }
          modalTitle.textContent = 'Add Template';
          modal.show();
     });

     grid.addEventListener('click', function(event) {
          var editBtn = event.target.closest('.btn-edit-template');
          var deleteBtn = event.target.closest('.btn-delete-template');
          var restoreBtn = event.target.closest('.btn-restore-template');
          var regenBtn = event.target.closest('.btn-regen-template');
          if (editBtn === null && deleteBtn === null && restoreBtn === null && regenBtn === null) {
               return;
          }
          // These buttons sit on top of the card link.
          event.preventDefault();
          var item = event.target.closest('.template-item');
          var id = item.dataset.id;

          if (editBtn !== null) {
               clearError();
               form.reset();
               fieldMode.value = 'edit';
               fieldId.value = id;
               // The id is part of the file name, so editing it would orphan the file.
               fieldId.readOnly = true;
               fieldCategory.value = item.dataset.category;
               fieldName.value = item.dataset.name || '';
               fieldHtml.required = false;
               htmlHelp.textContent = 'Leave empty to keep the current HTML file.';
               if (autoThumb !== null) {
                    // Regenerating replaces an existing thumbnail, so make it opt-in here.
                    autoThumb.checked = false;
                    autoThumbHelp.textContent = 'Re-renders the thumbnail from the template and replaces the current one.';
               }
               modalTitle.textContent = 'Edit Template ' + id;
               modal.show();
               return;
          }

          if (regenBtn !== null) {
               regenBtn.disabled = true;
               var spinner = regenBtn.innerHTML;
               regenBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
               var revert = function() {
                    regenBtn.disabled = false;
                    regenBtn.innerHTML = spinner;
               };
               var payload = new FormData();
               payload.append('mode', 'regen');
               payload.append('id', id);
               send(payload, function(result) {
                    if (result.status === 'success') {
                         window.location.reload();
                         return;
                    }
                    revert();
                    window.alert(result.message || 'The thumbnail could not be rendered.');
               }, function(message) {
                    revert();
                    window.alert(message);
               });
               return;
          }

          if (deleteBtn !== null) {
               if (!window.confirm('Remove Template ' + id + ' from the list?\n\nThe uploaded files are kept and the template can be restored.')) {
                    return;
               }
               postAction({ mode: 'delete', id: id }, deleteBtn);
               return;
          }

          if (!window.confirm('Restore Template ' + id + ' to the list?')) {
               return;
          }
          postAction({ mode: 'restore', id: id }, restoreBtn);
     });

     function postAction(fields, button) {
          var data = new FormData();
          Object.keys(fields).forEach(function(key) {
               data.append(key, fields[key]);
          });
          if (button) {
               button.disabled = true;
          }
          send(data, function(result) {
               if (result.status === 'success') {
                    window.location.reload();
                    return;
               }
               if (button) {
                    button.disabled = false;
               }
               window.alert(result.message || 'The request failed.');
          }, function(message) {
               if (button) {
                    button.disabled = false;
               }
               window.alert(message);
          });
     }

     form.addEventListener('submit', function(event) {
          event.preventDefault();
          clearError();

          var file = fieldHtml.files.length > 0 ? fieldHtml.files[0] : null;
          if (fieldMode.value === 'add' && file === null) {
               showError('Please choose an HTML file.');
               return;
          }
          // Mirrors the server-side extension check so the user gets a fast answer.
          if (file !== null && !/\.html?$/i.test(file.name)) {
               showError('Only .html or .htm files can be uploaded.');
               return;
          }
          var thumb = fieldThumb.files.length > 0 ? fieldThumb.files[0] : null;
          if (thumb !== null && !/\.(png|jpe?g|webp)$/i.test(thumb.name)) {
               showError('The thumbnail must be a PNG, JPG or WebP image.');
               return;
          }

          // Rendering a thumbnail takes a few seconds, so show progress.
          var rendering = autoThumb !== null && autoThumb.checked && thumb === null;
          submitBtn.disabled = true;
          submitBtn.dataset.label = submitBtn.textContent;
          submitBtn.textContent = rendering ? 'Rendering thumbnail...' : 'Saving...';

          function restoreButton() {
               submitBtn.disabled = false;
               submitBtn.textContent = submitBtn.dataset.label || 'Save';
          }

          send(new FormData(form), function(result) {
               if (result.status === 'success') {
                    // A failed render still saves the template; say so before reloading.
                    if (result.notice) {
                         window.alert(result.message);
                    }
                    window.location.reload();
                    return;
               }
               restoreButton();
               showError(result.message || 'The request failed.');
          }, function(message) {
               restoreButton();
               showError(message);
          });
     });

     // ---- Regenerate thumbnails ----
     var regenModalEl = document.getElementById('regenModal');
     var regenModal = regenModalEl !== null ? new bootstrap.Modal(regenModalEl) : null;
     var btnRegenThumbs = document.getElementById('btnRegenThumbs');

     if (btnRegenThumbs !== null && regenModal !== null) {
          var regenStart = document.getElementById('regenStart');
          var regenCancel = document.getElementById('regenCancel');
          var regenClose = document.getElementById('regenClose');
          var regenAlert = document.getElementById('regenAlert');
          var regenPickWrap = document.getElementById('regenPickWrap');
          var regenProgressWrap = document.getElementById('regenProgressWrap');
          var regenProgressBar = document.getElementById('regenProgressBar');
          var regenProgressCount = document.getElementById('regenProgressCount');
          var regenProgressLabel = document.getElementById('regenProgressLabel');
          var regenResults = document.getElementById('regenResults');
          var regenScopeMissing = document.getElementById('regenScopeMissing');
          var regenAnyDone = false;

          function regenTargets(scope) {
               return items.filter(function(item) {
                    if (item.dataset.active !== '1') {
                         return false;
                    }
                    return scope === 'all' || item.dataset.hasthumb === '0';
               });
          }

          btnRegenThumbs.addEventListener('click', function() {
               regenAlert.classList.add('d-none');
               regenResults.innerHTML = '';
               regenProgressWrap.classList.add('d-none');
               regenPickWrap.classList.remove('d-none');
               regenStart.classList.remove('d-none');
               regenStart.disabled = false;
               regenCancel.textContent = 'Cancel';
               regenAnyDone = false;
               document.getElementById('regenMissingCount').textContent = regenTargets('missing').length;
               document.getElementById('regenAllCount').textContent = regenTargets('all').length;
               regenModal.show();
          });

          regenModalEl.addEventListener('hidden.bs.modal', function() {
               if (regenAnyDone) {
                    window.location.reload();
               }
          });

          regenStart.addEventListener('click', function() {
               regenAlert.classList.add('d-none');
               var scope = regenScopeMissing.checked ? 'missing' : 'all';
               var targets = regenTargets(scope);
               if (targets.length === 0) {
                    regenAlert.textContent = scope === 'missing' ?
                         'Every visible template already has a thumbnail.' :
                         'There are no templates to render.';
                    regenAlert.classList.remove('d-none');
                    return;
               }

               regenPickWrap.classList.add('d-none');
               regenStart.classList.add('d-none');
               regenCancel.disabled = true;
               regenClose.disabled = true;
               regenProgressWrap.classList.remove('d-none');

               var done = 0;
               var okCount = 0;
               var failCount = 0;

               function updateProgress() {
                    regenProgressBar.style.width = Math.round((done / targets.length) * 100) + '%';
                    regenProgressCount.textContent = done + ' / ' + targets.length;
               }
               updateProgress();

               function addResult(label, ok, message) {
                    var li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.innerHTML = '<i class="bi ' + (ok ? 'bi-check-circle-fill text-success' :
                         'bi-x-circle-fill text-danger') + '"></i> <strong>' + label + '</strong>' +
                         (message ? '<div class="small text-muted">' + message + '</div>' : '');
                    regenResults.appendChild(li);
                    regenResults.scrollTop = regenResults.scrollHeight;
               }

               // One request per template keeps each render inside the server timeout.
               function next(index) {
                    if (index >= targets.length) {
                         regenProgressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                         regenProgressLabel.textContent = 'Finished: ' + okCount + ' rendered, ' +
                              failCount + ' failed';
                         regenCancel.disabled = false;
                         regenClose.disabled = false;
                         regenCancel.textContent = 'Close';
                         return;
                    }
                    var item = targets[index];
                    var id = item.dataset.id;
                    var label = item.dataset.name || ('Template ' + id);
                    regenProgressLabel.textContent = 'Rendering ' + label;

                    var data = new FormData();
                    data.append('mode', 'regen');
                    data.append('id', id);

                    send(data, function(result) {
                         done++;
                         if (result.status === 'success') {
                              okCount++;
                              regenAnyDone = true;
                              addResult(label, true, 'Thumbnail rendered.');
                         } else {
                              failCount++;
                              addResult(label, false, result.message || 'Failed.');
                         }
                         updateProgress();
                         next(index + 1);
                    }, function(message) {
                         done++;
                         failCount++;
                         addResult(label, false, message);
                         updateProgress();
                         next(index + 1);
                    });
               }
               next(0);
          });
     }

     // ---- Bulk upload ----
     var bulkModalEl = document.getElementById('bulkModal');
     var bulkModal = new bootstrap.Modal(bulkModalEl);
     var bulkFiles = document.getElementById('bulkFiles');
     var bulkCategory = document.getElementById('bulkCategory');
     var bulkAutoThumb = document.getElementById('bulkAutoThumb');
     var bulkStart = document.getElementById('bulkStart');
     var bulkCancel = document.getElementById('bulkCancel');
     var bulkClose = document.getElementById('bulkClose');
     var bulkAlert = document.getElementById('bulkAlert');
     var bulkProgressWrap = document.getElementById('bulkProgressWrap');
     var bulkProgressBar = document.getElementById('bulkProgressBar');
     var bulkProgressCount = document.getElementById('bulkProgressCount');
     var bulkProgressLabel = document.getElementById('bulkProgressLabel');
     var bulkResults = document.getElementById('bulkResults');
     var bulkPickWrap = document.getElementById('bulkPickWrap');
     var bulkCategoryWrap = document.getElementById('bulkCategoryWrap');
     var bulkAutoWrap = document.getElementById('bulkAutoWrap');
     var bulkAnySaved = false;
     var bulkRunning = false;

     document.getElementById('btnBulkTemplate').addEventListener('click', function() {
          bulkAlert.classList.add('d-none');
          bulkFiles.value = '';
          bulkResults.innerHTML = '';
          bulkProgressWrap.classList.add('d-none');
          bulkPickWrap.classList.remove('d-none');
          bulkCategoryWrap.classList.remove('d-none');
          if (bulkAutoWrap !== null) {
               bulkAutoWrap.classList.remove('d-none');
          }
          bulkStart.classList.remove('d-none');
          bulkStart.disabled = false;
          bulkStart.textContent = 'Upload';
          bulkCancel.textContent = 'Cancel';
          bulkAnySaved = false;
          bulkRunning = false;
          bulkModal.show();
     });

     // Reload once, on close, so the grid picks up everything that was added.
     bulkModalEl.addEventListener('hidden.bs.modal', function() {
          if (bulkAnySaved) {
               window.location.reload();
          }
     });

     function bulkAddResult(name, ok, message) {
          var item = document.createElement('li');
          item.className = 'list-group-item d-flex justify-content-between align-items-start gap-2';
          var left = document.createElement('div');
          left.className = 'text-break';
          left.innerHTML = '<i class="bi ' + (ok ? 'bi-check-circle-fill text-success' :
               'bi-x-circle-fill text-danger') + '"></i> <strong>' + name + '</strong>' +
               (message ? '<div class="small text-muted">' + message + '</div>' : '');
          item.appendChild(left);
          bulkResults.appendChild(item);
          bulkResults.scrollTop = bulkResults.scrollHeight;
     }

     bulkStart.addEventListener('click', function() {
          bulkAlert.classList.add('d-none');
          var files = Array.prototype.slice.call(bulkFiles.files);
          if (files.length === 0) {
               bulkAlert.textContent = 'Please choose at least one HTML file.';
               bulkAlert.classList.remove('d-none');
               return;
          }
          var bad = files.filter(function(file) {
               return !/\.html?$/i.test(file.name);
          });
          if (bad.length > 0) {
               bulkAlert.textContent = 'Only .html or .htm files can be uploaded. Remove: ' +
                    bad.map(function(f) { return f.name; }).join(', ');
               bulkAlert.classList.remove('d-none');
               return;
          }

          // Lock the form; the batch is not safely restartable midway.
          bulkRunning = true;
          bulkPickWrap.classList.add('d-none');
          bulkCategoryWrap.classList.add('d-none');
          if (bulkAutoWrap !== null) {
               bulkAutoWrap.classList.add('d-none');
          }
          bulkStart.classList.add('d-none');
          bulkCancel.disabled = true;
          bulkClose.disabled = true;
          bulkProgressWrap.classList.remove('d-none');

          var category = bulkCategory.value;
          var auto = bulkAutoThumb !== null && bulkAutoThumb.checked;
          var done = 0;
          var okCount = 0;
          var failCount = 0;

          function updateProgress() {
               var pct = Math.round((done / files.length) * 100);
               bulkProgressBar.style.width = pct + '%';
               bulkProgressCount.textContent = done + ' / ' + files.length;
          }
          updateProgress();

          // One request per file keeps each one well inside any server timeout.
          function next(index) {
               if (index >= files.length) {
                    bulkRunning = false;
                    bulkProgressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                    bulkProgressLabel.textContent = 'Finished: ' + okCount + ' added, ' + failCount + ' failed';
                    bulkCancel.disabled = false;
                    bulkClose.disabled = false;
                    bulkCancel.textContent = 'Close';
                    return;
               }
               var file = files[index];
               bulkProgressLabel.textContent = (auto ? 'Rendering ' : 'Uploading ') + file.name;

               var data = new FormData();
               data.append('mode', 'add');
               data.append('category', category);
               if (auto) {
                    data.append('autoThumb', '1');
               }
               // No id: the server derives it from the file name.
               data.append('htmlFile', file);

               send(data, function(result) {
                    done++;
                    if (result.status === 'success') {
                         okCount++;
                         bulkAnySaved = true;
                         bulkAddResult(file.name, true, 'Saved as template ' + (result.id || '?') +
                              (result.notice ? ' - ' + result.notice : ''));
                    } else {
                         failCount++;
                         bulkAddResult(file.name, false, result.message || 'Failed.');
                    }
                    updateProgress();
                    next(index + 1);
               }, function(message) {
                    done++;
                    failCount++;
                    bulkAddResult(file.name, false, message);
                    updateProgress();
                    next(index + 1);
               });
          }
          next(0);
     });

     function send(data, onDone, onFail) {
          fetch(endpoint, { method: 'POST', body: data, credentials: 'same-origin' })
               .then(function(response) {
                    return response.json().then(function(json) {
                         return json;
                    }).catch(function() {
                         throw new Error('The server returned an unexpected response (HTTP ' + response.status + ').');
                    });
               })
               .then(onDone)
               .catch(function(error) {
                    onFail(error.message || 'The request could not be sent.');
               });
     }
})();
</script>

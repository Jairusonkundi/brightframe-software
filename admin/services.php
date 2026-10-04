<?php
/**
 * Admin: manage service categories and individual services, instead of
 * requiring direct database edits every time the catalog changes.
 *
 * One note worth knowing: each category also has a dedicated detail page
 * at services/<slug>.php — a thin static file that defers to
 * includes/category-page.php, which renders from the database. Adding a
 * new category here now auto-creates that file (a 3-line shim), so it
 * gets its own page immediately with fallback editorial content. The
 * hand-written "why this matters" / "types of X" copy lives in
 * includes/category-content.php, keyed by slug, and only the original 8
 * categories have bespoke copy — a new category will show shorter
 * generic fallback text until that copy is added by hand (ask for this
 * if you want it). Renaming an EXISTING category's slug will break its
 * existing services/<slug>.php page the same way, since that file is
 * keyed to the old slug — flagged inline on the edit form.
 *
 * icon_name is intentionally not user-editable here: the established
 * convention (see includes/icons.php) is that every service in a
 * category shares one icon, keyed by the category's slug — so it's
 * always auto-set to match, on both add and edit.
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

function slugify(string $text): string
{
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Ensure a services/<slug>.php shim file exists for a category, writing it
 * if missing. Each category's dedicated detail page is a tiny static file
 * that sets $categorySlug and includes includes/category-page.php (which
 * renders from the database); admin-created categories otherwise have no
 * such file and would fall back to services.php#cat-<slug> instead of
 * getting their own page.
 *
 * The file is only created when it does not already exist, so an existing
 * page (with hand-written editorial content) is never overwritten. Returns
 * true on success (or if a file already existed), false if it couldn't be
 * written.
 */
function ensure_category_page(string $slug): bool
{
    $path = __DIR__ . '/../services/' . $slug . '.php';
    if (file_exists($path)) {
        return true;
    }
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        return false;
    }
    $content = "<?php\n" .
        '$categorySlug = ' . var_export($slug, true) . ";\n" .
        "require __DIR__ . '/../includes/category-page.php';\n";
    return @file_put_contents($path, $content) !== false;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_category') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $errors[] = 'Category name is required.';
            } else {
                // trim($_POST['slug'] ?? '') !== '' rather than
                // ($_POST['slug'] ?? $name): the slug field is always
                // present in the submitted form (just possibly empty
                // when left blank), so ?? never actually falls back —
                // an empty string satisfies "isset", it's not null.
                $slugInput = trim($_POST['slug'] ?? '');
                $slug = slugify($slugInput !== '' ? $slugInput : $name);
                if ($slug === '') {
                    $errors[] = 'Could not generate a slug from that name — try adding letters or numbers.';
                } else {
                    $maxOrder = (int) $pdo->query('SELECT COALESCE(MAX(display_order), 0) FROM service_categories')->fetchColumn();
                    $stmt = $pdo->prepare('INSERT INTO service_categories (name, slug, display_order) VALUES (:name, :slug, :order)');
                    $stmt->execute(['name' => $name, 'slug' => $slug, 'order' => $maxOrder + 1]);

                    // Give the new category its own dedicated page right
                    // away. If that write fails, continue anyway — the
                    // category still works everywhere via the fallback link
                    // (services.php#cat-<slug>), so it's not fatal.
                    if (!ensure_category_page($slug)) {
                        error_log('Could not create category page for slug: ' . $slug);
                    }
                }
            }
        } elseif ($action === 'edit_category') {
            $id   = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = slugify($_POST['slug'] ?? '');
            if ($id > 0 && $name !== '' && $slug !== '') {
                $stmt = $pdo->prepare('UPDATE service_categories SET name = :name, slug = :slug WHERE id = :id');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'id' => $id]);
            } elseif ($id > 0) {
                $errors[] = 'Category name and slug can\'t be empty.';
            }
        } elseif ($action === 'delete_category') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare('DELETE FROM service_categories WHERE id = :id')->execute(['id' => $id]);
            }
        } elseif ($action === 'add_service') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $title      = trim($_POST['title'] ?? '');
            $desc       = trim($_POST['description'] ?? '');
            $longDesc   = trim($_POST['long_description'] ?? '');
            $order      = (int) ($_POST['display_order'] ?? 0);
            if ($categoryId > 0 && $title !== '' && $desc !== '') {
                $catSlug = $pdo->prepare('SELECT slug FROM service_categories WHERE id = :id');
                $catSlug->execute(['id' => $categoryId]);
                $slug = $catSlug->fetchColumn();
                if ($slug) {
                    $stmt = $pdo->prepare(
                        'INSERT INTO services (category_id, title, description, long_description, icon_name, display_order)
                         VALUES (:cat, :title, :desc, :long, :icon, :order)'
                    );
                    $stmt->execute([
                        'cat' => $categoryId, 'title' => $title, 'desc' => $desc,
                        'long' => $longDesc !== '' ? $longDesc : null, 'icon' => $slug, 'order' => $order,
                    ]);
                } else {
                    $errors[] = 'Could not find that category.';
                }
            } else {
                $errors[] = 'Service title and description are required.';
            }
        } elseif ($action === 'edit_service') {
            $id         = (int) ($_POST['id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $title      = trim($_POST['title'] ?? '');
            $desc       = trim($_POST['description'] ?? '');
            $longDesc   = trim($_POST['long_description'] ?? '');
            $order      = (int) ($_POST['display_order'] ?? 0);
            if ($id > 0 && $categoryId > 0 && $title !== '' && $desc !== '') {
                $catSlug = $pdo->prepare('SELECT slug FROM service_categories WHERE id = :id');
                $catSlug->execute(['id' => $categoryId]);
                $slug = $catSlug->fetchColumn();
                if ($slug) {
                    $stmt = $pdo->prepare(
                        'UPDATE services SET category_id = :cat, title = :title, description = :desc,
                         long_description = :long, icon_name = :icon, display_order = :order WHERE id = :id'
                    );
                    $stmt->execute([
                        'cat' => $categoryId, 'title' => $title, 'desc' => $desc,
                        'long' => $longDesc !== '' ? $longDesc : null, 'icon' => $slug, 'order' => $order, 'id' => $id,
                    ]);
                }
            }
        } elseif ($action === 'delete_service') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare('DELETE FROM services WHERE id = :id')->execute(['id' => $id]);
            }
        }
    } catch (PDOException $e) {
        error_log('Failed to save services/categories change: ' . $e->getMessage());
        if ($e->getCode() === '23000') {
            $errors[] = 'That slug is already used by another category.';
        } else {
            $errors[] = 'Something went wrong saving that change.';
        }
    }

    if (empty($errors)) {
        header('Location: services.php?saved=1');
        exit;
    }
}

try {
    $categories = $pdo->query('SELECT id, name, slug, display_order FROM service_categories ORDER BY display_order ASC')->fetchAll();
    $allServices = $pdo->query(
        'SELECT id, category_id, title, description, long_description, display_order FROM services ORDER BY category_id, display_order ASC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load services/categories: ' . $e->getMessage());
    $categories  = [];
    $allServices = [];
}

$servicesByCategory = [];
foreach ($allServices as $svc) {
    $servicesByCategory[$svc['category_id']][] = $svc;
}

$pageTitle   = 'Services';
$adminActive = 'services';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Services</h1>
    <p class="admin-page-sub"><?= count($categories) ?> categories, <?= count($allServices) ?> services — all live on <a href="../services.php" target="_blank" rel="noopener">services.php</a> and throughout the site.</p>
  </div>
  <details class="admin-reject-details">
    <summary class="admin-btn admin-btn-primary">+ Add category</summary>
    <form method="post" class="admin-modal-form" style="margin-top: 12px;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="action" value="add_category">
      <div class="admin-field">
        <label for="new-cat-name">Category name</label>
        <input id="new-cat-name" name="name" type="text" required placeholder="e.g. Video Production">
      </div>
      <div class="admin-field">
        <label for="new-cat-slug">Slug <span class="admin-field-hint">optional — auto-generated from the name if left blank</span></label>
        <input id="new-cat-slug" name="slug" type="text" placeholder="e.g. video-production">
      </div>
      <p class="admin-field-hint" style="margin-top:-6px;">A matching services/&lt;slug&gt;.php page is created automatically. Note: new categories start with short generic placeholder copy — bespoke "why it matters" and "types of X" content needs to be added by hand later.</p>
      <button type="submit" class="admin-btn admin-btn-primary">Create category</button>
    </form>
  </details>
</div>

<?php if (isset($_GET['saved'])): ?>
  <p class="admin-saved-note">Saved.</p>
<?php endif; ?>
<?php foreach ($errors as $error): ?>
  <p class="admin-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endforeach; ?>

<?php if (empty($categories)): ?>
  <div class="admin-panel">
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg>
      </span>
      <p class="admin-empty-title">No categories yet</p>
      <p class="admin-empty-note">Add one above to get started.</p>
    </div>
  </div>
<?php else: ?>
  <?php foreach ($categories as $cat): ?>
    <?php $catServices = $servicesByCategory[$cat['id']] ?? []; ?>
    <div class="admin-cat-block">
      <div class="admin-cat-head">
        <div>
          <span class="admin-cat-head-name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
          <span class="admin-cat-head-slug">/services/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>.php · <?= count($catServices) ?> service<?= count($catServices) === 1 ? '' : 's' ?></span>
        </div>
        <div class="admin-cat-actions">
          <details class="admin-reject-details">
            <summary class="admin-btn">Edit</summary>
            <form method="post" class="admin-modal-form admin-inline-panel">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="action" value="edit_category">
              <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
              <div class="admin-form-row">
                <div class="admin-field">
                  <label for="cat-name-<?= (int) $cat['id'] ?>">Name</label>
                  <input id="cat-name-<?= (int) $cat['id'] ?>" name="name" type="text" value="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="admin-field">
                  <label for="cat-slug-<?= (int) $cat['id'] ?>">Slug</label>
                  <input id="cat-slug-<?= (int) $cat['id'] ?>" name="slug" type="text" value="<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
              </div>
              <p class="admin-field-hint">Changing the slug will break this category's services/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>.php page if one exists (that file looks up the exact old slug) — a developer would need to update it to match.</p>
              <button type="submit" class="admin-btn admin-btn-primary" style="align-self: flex-start;">Save category</button>
            </form>
          </details>
          <details class="admin-reject-details">
            <summary class="admin-btn">+ Add service</summary>
            <form method="post" class="admin-modal-form admin-inline-panel">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="action" value="add_service">
              <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
              <div class="admin-field">
                <label for="new-svc-title-<?= (int) $cat['id'] ?>">Title</label>
                <input id="new-svc-title-<?= (int) $cat['id'] ?>" name="title" type="text" required>
              </div>
              <div class="admin-field">
                <label for="new-svc-desc-<?= (int) $cat['id'] ?>">Short description <span class="admin-field-hint">one line, shown in compact contexts</span></label>
                <textarea id="new-svc-desc-<?= (int) $cat['id'] ?>" name="description" required></textarea>
              </div>
              <div class="admin-field">
                <label for="new-svc-long-<?= (int) $cat['id'] ?>">Long description <span class="admin-field-hint">optional, 2-3 sentences, shown on the category detail page</span></label>
                <textarea id="new-svc-long-<?= (int) $cat['id'] ?>" name="long_description"></textarea>
              </div>
              <button type="submit" class="admin-btn admin-btn-primary" style="align-self: flex-start;">Add service</button>
            </form>
          </details>
          <form method="post" onsubmit="return confirm('Delete &quot;<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>&quot; and all <?= count($catServices) ?> of its services? This cannot be undone.');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
            <button type="submit" class="admin-btn admin-btn-reject">Delete</button>
          </form>
        </div>
      </div>

      <?php if (empty($catServices)): ?>
        <div class="admin-svc-row"><span class="admin-svc-row-desc">No services in this category yet.</span></div>
      <?php else: ?>
        <?php foreach ($catServices as $svc): ?>
          <div class="admin-svc-row">
            <div>
              <div class="admin-svc-row-title"><?= htmlspecialchars($svc['title'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="admin-svc-row-desc"><?= htmlspecialchars($svc['description'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="admin-svc-row-actions">
              <details class="admin-reject-details">
                <summary class="admin-btn">Edit</summary>
                <form method="post" class="admin-modal-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action" value="edit_service">
                  <input type="hidden" name="id" value="<?= (int) $svc['id'] ?>">
                  <div class="admin-field">
                    <label for="svc-cat-<?= (int) $svc['id'] ?>">Category</label>
                    <select id="svc-cat-<?= (int) $svc['id'] ?>" name="category_id">
                      <?php foreach ($categories as $optCat): ?>
                        <option value="<?= (int) $optCat['id'] ?>"<?= $optCat['id'] === $svc['category_id'] ? ' selected' : '' ?>><?= htmlspecialchars($optCat['name'], ENT_QUOTES, 'UTF-8') ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="admin-field">
                    <label for="svc-title-<?= (int) $svc['id'] ?>">Title</label>
                    <input id="svc-title-<?= (int) $svc['id'] ?>" name="title" type="text" value="<?= htmlspecialchars($svc['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                  </div>
                  <div class="admin-field">
                    <label for="svc-desc-<?= (int) $svc['id'] ?>">Short description</label>
                    <textarea id="svc-desc-<?= (int) $svc['id'] ?>" name="description" required><?= htmlspecialchars($svc['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                  </div>
                  <div class="admin-field">
                    <label for="svc-long-<?= (int) $svc['id'] ?>">Long description</label>
                    <textarea id="svc-long-<?= (int) $svc['id'] ?>" name="long_description"><?= htmlspecialchars($svc['long_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                  </div>
                  <div class="admin-field">
                    <label for="svc-order-<?= (int) $svc['id'] ?>">Display order</label>
                    <input id="svc-order-<?= (int) $svc['id'] ?>" name="display_order" type="number" value="<?= (int) $svc['display_order'] ?>">
                  </div>
                  <button type="submit" class="admin-btn admin-btn-primary" style="align-self: flex-start;">Save service</button>
                </form>
              </details>
              <form method="post" onsubmit="return confirm('Delete &quot;<?= htmlspecialchars($svc['title'], ENT_QUOTES, 'UTF-8') ?>&quot;?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="delete_service">
                <input type="hidden" name="id" value="<?= (int) $svc['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-reject">Delete</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>

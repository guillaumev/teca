<div id="wrap">
  <header id="navbar" role="banner" class="navbar navbar-default container-fluid">
      <?php if (!empty($page['navigation'])): ?>
        <div class="container">
          <nav role="banner">
            <?php if (!empty($primary_nav)): ?>
              <?php print render($primary_nav); ?>
            <?php endif; ?>
            <?php if (!empty($secondary_nav)): ?>
              <?php print render($secondary_nav); ?>
            <?php endif; ?>
            <?php if (!empty($page['navigation'])): ?>
              <?php print render($page['navigation']); ?>
            <?php endif; ?>
          </nav>
        </div><!-- .container -->
      <?php endif; ?>
  </header>

  <div class="main-container container">

    <div class="row">

      <?php if (!empty($page['sidebar_first'])): ?>
        <aside class="col-sm-3 section-sidebar-first" role="complementary">
          <div id="fao-logo">
            <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><img src="<?php print base_path().path_to_theme(); ?>/images/logos/logo_<?php print $language->language; ?>.png" alt="<?php print t('Home'); ?>" /></a>
          </div>
          <?php print render($page['sidebar_first']); ?>
        </aside>  <!-- /#sidebar-first -->
      <?php endif; ?>

      <section<?php print $content_column_class; ?>>
        <header role="banner" id="page-header">
        <?php if ($logo): ?>
          <a class="logo" href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>">
            <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" />
          </a>
          <?php endif; ?>
        <?php if (!empty($site_slogan)): ?>
          <p class="lead"><?php print $site_slogan; ?></p>
        <?php endif; ?>

        <?php print render($page['header']); ?>
      </header> <!-- /#header -->
      <div id="content">
        <?php if (!empty($page['highlighted'])): ?>
          <div class="highlighted hero-unit"><?php print render($page['highlighted']); ?></div>
        <?php endif; ?>
        <?php if (!empty($breadcrumb)): print $breadcrumb; endif;?>
        <a id="main-content"></a>
        <?php print render($title_prefix); ?>
        <?php if (!empty($title)): ?>
          <h1 class="page-header"><?php print $title; ?></h1>
        <?php endif; ?>
        <?php print render($title_suffix); ?>
        <?php print $messages; ?>
        <?php if (!empty($tabs)): ?>
          <?php print render($tabs); ?>
        <?php endif; ?>
        <?php if (!empty($page['help'])): ?>
          <?php print render($page['help']); ?>
        <?php endif; ?>
        <?php if (!empty($action_links)): ?>
          <ul class="action-links"><?php print render($action_links); ?></ul>
        <?php endif; ?>
        <?php print render($page['content']); ?>
      </div>
      </section>

      <?php if (!empty($page['sidebar_second'])): ?>
        <aside class="col-sm-3 section-sidebar-second" role="complementary">
          <?php print render($page['sidebar_second']); ?>
        </aside>  <!-- /#sidebar-second -->
      <?php endif; ?>

    </div>
  </div>
  </div>
</div><!-- #wrap -->

<footer class="footer container-fluid">
  <?php print render($page['footer']); ?>
</footer>

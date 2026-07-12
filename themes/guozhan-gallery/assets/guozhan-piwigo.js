(function () {
  var body = document.body;
  var app = document.getElementById("guozhan-app");
  if (!body || !app) return;

  var params = new URLSearchParams(window.location.search);
  var page = params.get("gz_page") || inferPage();
  var brandName = "图物计划国展素材馆";
  var brandEn = "AI Graphics Learning Plan";
  var assetBase = window.GUOZHAN_ASSET_BASE || "/themes/guozhan-gallery/assets/";

  function siteRoot() {
    var url = new URL(window.GUOZHAN_ROOT_URL || "./", window.location.href);
    url.search = "";
    url.hash = "";
    if (url.pathname.slice(-1) !== "/") url.pathname += "/";
    return url;
  }

  function appUrl(nextPage, extra) {
    var url = siteRoot();
    if (nextPage && nextPage !== "home") url.searchParams.set("gz_page", nextPage);
    Object.keys(extra || {}).forEach(function (key) {
      if (extra[key] !== undefined && extra[key] !== null && extra[key] !== "") {
        url.searchParams.set(key, extra[key]);
      }
    });
    return url.pathname + url.search;
  }

  window.GUOZHAN_APP_URL = appUrl;

  function inferPage() {
    var bodyId = body.getAttribute("data-piwigo-body-id") || body.id || "";
    var href = window.location.href;
    if (bodyId === "thePicturePage") return "detail";
    if (/search|qsearch/i.test(window.location.pathname)) return "search";
    if (href.indexOf("?/category/") >= 0 || params.has("cat_id")) return "category";
    return "home";
  }

  function setMode(name) {
    ["data-home-page", "data-categories-page", "data-category-page", "data-search-page", "data-detail-page", "data-contact-page", "data-competition-page"].forEach(function (attr) {
      body.removeAttribute(attr);
    });
    body.setAttribute("data-" + (name === "category" ? "category" : name) + "-page", "");
  }

  function current(name, active) {
    return name === active ? ' aria-current="page"' : "";
  }

  function nav(active) {
    return '<header class="topbar" data-od-id="site-header"><div class="container topbar-inner">' +
      '<a class="brand" href="' + appUrl("home") + '" aria-label="返回首页"><span class="brand-mark"><img src="' + assetBase + 'logo-mark.png?v=20260712-logo-fulltext" alt=""></span><span class="brand-text"><strong>' + brandName + '</strong><span>' + brandEn + '</span></span></a>' +
      '<nav class="nav" data-nav aria-label="主导航">' +
        '<a href="' + appUrl("home") + '"' + current("home", active) + '>首页</a>' +
        '<a href="' + appUrl("categories") + '"' + current("categories", active) + '>作品分类</a>' +
        '<a href="' + appUrl("competition") + '"' + current("competition", active) + '>比赛</a>' +
        '<a href="' + appUrl("search") + '"' + current("search", active) + '>搜索</a>' +
        '<a href="' + appUrl("contact") + '"' + current("contact", active) + '>联系客服</a>' +
      '</nav><div class="nav-actions"><button class="menu-toggle" type="button" data-menu-toggle aria-label="打开导航" aria-expanded="false">☰</button></div>' +
    '</div></header>';
  }

  function searchForm(inputId, placeholder, compact, targetPage) {
    var searchTarget = targetPage || "search";
    return '<form class="hero-search" action="' + siteRoot().pathname + '" method="get" data-search-form>' +
      '<input type="hidden" name="gz_page" value="' + searchTarget + '">' +
      '<div class="search-form"><label class="small" style="position:absolute;clip:rect(0 0 0 0);" for="' + inputId + '">搜索作品</label>' +
      '<input id="' + inputId + '" class="search-input" name="q" placeholder="' + placeholder + '" autocomplete="off">' +
      '<button class="btn btn-primary" type="submit">' + (compact ? "搜索" : "搜索作品") + '</button></div></form>';
  }

  function quickTags() {
    var tags = ["国画", "油画", "版画", "中美协展览专项画稿", "未知画展"];
    return '<div class="quick-tags" aria-label="热门搜索">' + tags.map(function (tag) {
      return '<a class="tag" href="' + appUrl("search", { q: tag }) + '">' + tag + '</a>';
    }).join("") + '</div>';
  }

  function pageHero(id, crumbs, title, lead, extra) {
    var crumbHtml = crumbs.map(function (item, index) {
      if (index === 0) return '<a href="' + appUrl("home") + '">' + item + '</a>';
      if (item === "作品分类") return '<a href="' + appUrl("categories") + '">' + item + '</a>';
      if (item === "比赛") return '<a href="' + appUrl("competition") + '">' + item + '</a>';
      return '<span>' + item + '</span>';
    }).join("<span>/</span>");
    return '<section class="page-hero" data-od-id="' + id + '"><div class="container"><nav class="breadcrumb" aria-label="面包屑">' + crumbHtml + '</nav><h1>' + title + '</h1><p class="lead">' + lead + '</p>' + (extra || "") + '</div></section>';
  }

  function homeMain() {
    return '<main><section class="hero" data-od-id="home-hero"><div class="hero-canvas" role="img" aria-label="国展作品素材展厅"><div class="hero-slide"></div><div class="hero-slide"></div><div class="hero-slide"></div></div><div class="container"><div class="hero-content"><h1>国展参赛作品素材库</h1><p class="hero-lead">围绕国画、油画、版画、雕塑、漆画、水彩及比赛专项整理作品，按分类、题材与编号高效定位所需素材。</p>' +
        searchForm("home-q", "输入作品编号、画种、题材或比赛关键词...") + quickTags() +
        '<div class="hero-meta"><span>作品图库</span><span>分类选稿</span><span>比赛作品</span></div></div></div></section>' +
      '<section class="section" data-od-id="home-directory"><div class="container"><div class="section-head"><div><p class="eyebrow">作品分类</p><h2>按画种与题材浏览</h2></div></div><div class="directory"></div></div></section>' +
      '<section class="section section-gallery-stream" data-od-id="home-featured-works"><div class="container"><div class="section-head"><div><h2>全部作品</h2></div></div><div class="recommend-filter home-sort" data-home-sort-group aria-label="首页作品排序"><button class="filter-chip" type="button" aria-pressed="true" data-home-sort="featured">精美作品</button><button class="filter-chip" type="button" aria-pressed="false" data-home-sort="downloads">下载量高</button><button class="filter-chip" type="button" aria-pressed="false" data-home-sort="hot">热门作品</button><button class="filter-chip" type="button" aria-pressed="false" data-home-sort="newest">最新作品</button></div><div class="gallery-grid stream-grid" data-home-stream></div><div class="pagination" data-home-pagination aria-label="首页作品分页"><button class="btn btn-secondary" type="button" data-home-prev>上一页</button><span data-home-status>第 1 / 1 页</span><button class="btn btn-primary" type="button" data-home-next>下一页</button></div></div></section></main>';
  }

  function categoriesMain() {
    return '<main>' + pageHero("categories-hero", ["首页", "作品目录"], "国展作品分类目录", "按画种、题材与展览专项归档作品，进入分类后可继续按编号、标签或关键词筛选。", searchForm("cat-q", "搜索：国画山水、雕塑圆雕、专项画稿、作品编号...", true)) +
      '<section class="section" data-od-id="categories-directory"><div class="container"><div class="section-head categories-directory-head"><div><p class="eyebrow categories-directory-title">分类目录</p></div></div><div class="directory"></div><div class="pagination directory-pagination" data-directory-pagination aria-label="分类分页"><button class="btn btn-secondary" type="button" data-page-prev>上一页</button><span data-page-status>第 1 / 1 页</span><button class="btn btn-primary" type="button" data-page-next>下一页</button></div></div></section></main>';
  }

  function categoryMain() {
    return '<main><section class="page-hero" data-od-id="category-list-hero"><div class="container"><nav class="breadcrumb" aria-label="面包屑"><a href="' + appUrl("home") + '">首页</a><span>/</span><a href="' + appUrl("categories") + '">作品分类</a><span>/</span><span data-category-parent>国画</span><span>/</span><span data-category-name>山水画</span></nav><h1 data-category-title>国画山水画</h1><p class="lead" data-category-desc>当前分类作品已按编号归档，可结合题材标签与排序方式进行选图。</p></div></section>' +
      '<section class="section" data-od-id="category-list-main"><div class="container category-layout"><aside class="side-panel"><p class="eyebrow">分类导航</p><h2><span data-category-parent>国画</span></h2><nav class="side-nav" aria-label="分类切换"></nav></aside><div><div class="toolbar">' + searchForm("list-q", "输入作品编号、题材标签或画面关键词...", true) + '<select class="select" aria-label="排序方式" data-sort-select><option value="custom">综合排序</option><option value="code">编号</option><option value="newest">最新</option><option value="hot">热门</option><option value="downloads">下载量</option></select></div><div class="filter-row" data-filter-group><button class="filter-chip" type="button" aria-pressed="true" data-filter>全部</button></div><p class="small" data-filter-status>当前查看：全部</p><div class="gallery-grid" style="margin-top:28px;" data-category-gallery></div><div class="pagination" data-pagination aria-label="作品分页"><button class="btn btn-secondary" type="button" data-page-prev>上一页</button><span data-page-status>第 1 / 1 页</span><button class="btn btn-primary" type="button" data-page-next>下一页</button></div></div></div></section></main>';
  }

  function competitionMain() {
    return '<main>' + pageHero("competition-hero", ["首页", "比赛"], "比赛", "按水墨、油画、版画、水彩等比赛方向整理作品，便于围绕赛事题材选图、比稿与编号咨询。", searchForm("competition-q", "搜索：水墨、油画、版画、水彩、作品编号...", true, "competition")) +
      '<section class="section" data-od-id="competition-main"><div class="container"><div class="section-head competition-filter-head"><div><p class="eyebrow competition-filter-title">比赛作品</p></div></div><div class="filter-row" data-filter-group><button class="filter-chip" type="button" aria-pressed="true" data-competition-filter="">全部</button><button class="filter-chip" type="button" aria-pressed="false" data-competition-filter="ink">水墨</button><button class="filter-chip" type="button" aria-pressed="false" data-competition-filter="oil">油画</button><button class="filter-chip" type="button" aria-pressed="false" data-competition-filter="print">版画</button><button class="filter-chip" type="button" aria-pressed="false" data-competition-filter="watercolor">水彩</button></div><div class="gallery-grid stream-grid" data-home-stream></div><div class="pagination" data-home-pagination aria-label="比赛作品分页"><button class="btn btn-secondary" type="button" data-home-prev>上一页</button><span data-home-status>第 1 / 1 页</span><button class="btn btn-primary" type="button" data-home-next>下一页</button></div></div></section></main>';
  }

  function searchMain() {
    return '<main>' + pageHero("search-hero", ["首页", "搜索"], "搜索作品", "输入作品编号、画种、题材或展览关键词，定位对应作品与分类。", searchForm("search-q", "搜索：GH-SS-001、国画山水、农民画、比赛作品...", true) + quickTags()) +
      '<section class="section" data-od-id="search-results"><div class="container"><div class="section-head"><div><p class="eyebrow">搜索结果</p><h2>关键词：<span data-search-term>国画</span></h2></div></div><div class="result-list" data-search-results></div><div class="pagination" data-pagination aria-label="搜索结果分页"><button class="btn btn-secondary" type="button" data-page-prev>上一页</button><span data-page-status>第 1 / 1 页</span><button class="btn btn-primary" type="button" data-page-next>下一页</button></div></div></section></main>';
  }

  function detailMain() {
    return '<main><section class="page-hero" data-od-id="detail-hero"><div class="container"><nav class="breadcrumb" aria-label="面包屑"><a href="' + appUrl("home") + '">首页</a><span>/</span><a href="' + appUrl("categories") + '">作品分类</a><span>/</span><a href="' + appUrl("category", { cat: "ink-landscape" }) + '" data-detail-category-link><span data-detail-category-title>国画山水画</span></a><span>/</span><span data-detail-code>GH-SS-001</span></nav><h1 data-detail-title>国画山水画 001</h1><p class="lead">核对作品大图、编号、分类与题材信息，可凭编号咨询高清素材。</p></div></section><section class="section" data-od-id="detail-main"><div class="container detail-layout"><div class="detail-art"><img src="' + (window.GUOZHAN_ASSET_BASE || "/themes/guozhan-gallery/assets/") + 'art-placeholder.svg" alt="作品大图"><div class="detail-code"><span>作品编号：<strong data-detail-code>GH-SS-001</strong></span><span>所属分类：<strong data-detail-category>国画 / 山水画</strong></span></div></div><aside><div class="info-panel"><p class="eyebrow">作品信息</p><h2 class="detail-title" data-detail-title>国画山水画 001</h2><div class="detail-list"><div class="detail-row"><span class="muted">作品编号</span><strong data-detail-code>GH-SS-001</strong></div><div class="detail-row"><span class="muted">所属分类</span><span data-detail-category>国画 / 山水画</span></div><div class="detail-row"><span class="muted">题材标签</span><span data-detail-tags>国画、山水画</span></div><div class="detail-row"><span class="muted">作品说明</span><span data-detail-desc>作品已完成分类归档，请记录编号以便确认高清素材与同类作品。</span></div></div><div class="contact-actions"><button class="btn btn-primary" type="button" data-open-contact>咨询高清素材</button><button class="btn btn-secondary" type="button" data-copy-code>复制作品编号</button></div></div><div class="info-panel"><p class="eyebrow">继续浏览</p><h2>查看同类作品</h2><div class="pager"><a class="btn btn-secondary" href="' + appUrl("category", { cat: "ink-landscape" }) + '" data-back-list>返回列表</a></div></div></aside></div></section><section class="section section-tight" data-od-id="related-works"><div class="container"><div class="section-head"><div><p class="eyebrow">同类作品</p><h2 data-related-title>同分类作品推荐</h2></div><a class="btn btn-link" href="' + appUrl("category", { cat: "ink-landscape" }) + '" data-related-more>查看全部</a></div><div class="gallery-grid" data-related-grid></div></div></section></main>';
  }

  function contactMain() {
    return '<main>' + pageHero("contact-hero", ["首页", "联系客服"], "联系客服", "需要确认高清素材、同类作品或使用方式时，请提供作品编号，客服将据此协助处理。") +
      '<section class="section" data-od-id="contact-main"><div class="container contact-band"><div><p class="eyebrow">客服二维码</p><div class="qr-box">客服二维码</div></div><div><h2>提供作品编号，精准确认所需素材</h2><p class="lead">客服可根据编号定位作品，协助确认高清素材、同类推荐与交付信息。</p><div class="detail-list"><div class="detail-row"><span class="muted">微信号</span><strong data-contact-wechat>aiguozhanhuihua</strong></div><div class="detail-row"><span class="muted">咨询范围</span><span>高清素材、同类作品、编号归档与使用方式</span></div><div class="detail-row"><span class="muted">咨询提示</span><span data-contact-note>建议提供作品编号或页面截图，便于准确确认作品。</span></div></div><div class="contact-actions"><button class="btn btn-primary" type="button" data-copy-wechat="aiguozhanhuihua">复制微信号</button><a class="btn btn-secondary" href="' + appUrl("categories") + '">继续浏览作品</a></div></div></div></section></main>';
  }

  function commonTail(text) {
    return '<footer class="footer"><div class="container footer-inner"><span data-footer-brand>© ' + brandName + '</span><span>' + text + '</span></div></footer>' +
      '<div class="modal-backdrop" data-contact-modal role="dialog" aria-modal="true" aria-label="客服联系方式"><div class="modal"><div class="modal-head"><div><h2>联系客服</h2><p class="muted">提供作品编号或页面截图，客服将协助确认高清素材、同类作品与使用方式。</p></div><button class="icon-button" type="button" data-close-contact aria-label="关闭">×</button></div><div class="contact-band"><div class="qr-box">客服二维码</div><div><p class="eyebrow">客服微信</p><h3 data-contact-wechat>aiguozhanhuihua</h3><p class="muted">复制微信号或扫码添加客服，提供作品编号即可确认素材。</p><div class="contact-actions"><button class="btn btn-primary" data-copy-wechat="aiguozhanhuihua">复制微信号</button><a class="btn btn-secondary" href="' + appUrl("contact") + '">查看联系方式页</a></div></div></div></div></div>' +
      '<div class="floating-service"><button class="btn btn-primary" type="button" data-open-contact>联系客服</button></div><div class="toast" data-toast role="status" aria-live="polite"></div>';
  }

  function render() {
    var active = "home";
    var main = homeMain();
    var footer = "作品图库 · 分类选稿 · 编号咨询";
    setMode("home");
    if (page === "categories") {
      active = "categories"; main = categoriesMain(); footer = "作品分类与专项归档"; setMode("categories");
    } else if (page === "category") {
      active = "categories"; main = categoryMain(); footer = "分类作品归档"; setMode("category");
    } else if (page === "competition") {
      active = "competition"; main = competitionMain(); footer = "比赛作品选稿"; setMode("competition");
    } else if (page === "search") {
      active = "search"; main = searchMain(); footer = "作品编号与分类检索"; setMode("search");
    } else if (page === "detail") {
      active = "categories"; main = detailMain(); footer = "编号咨询与高清素材确认"; setMode("detail");
    } else if (page === "contact") {
      active = "contact"; main = contactMain(); footer = "客服咨询与素材确认"; setMode("contact");
    }
    app.innerHTML = '<div class="site-shell">' + nav(active) + main + commonTail(footer) + '</div>';
    document.dispatchEvent(new CustomEvent("guozhan:rendered"));
  }

  render();
})();

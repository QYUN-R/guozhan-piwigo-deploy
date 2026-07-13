(function () {
  "use strict";

  function formatSize(bytes) {
    if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + " KB";
    return (bytes / 1024 / 1024).toFixed(1) + " MB";
  }

  function initMobileNavigation() {
    var sidebar = document.querySelector(".gzca-sidebar");
    var toggle = document.querySelector("[data-mobile-nav-toggle]");
    var nav = document.querySelector("[data-mobile-nav]");
    if (!sidebar || !toggle || !nav) return;

    function setOpen(open) {
      sidebar.classList.toggle("is-mobile-nav-open", open);
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      toggle.setAttribute("aria-label", open ? "收起后台菜单" : "展开后台菜单");
    }

    toggle.addEventListener("click", function () {
      setOpen(!sidebar.classList.contains("is-mobile-nav-open"));
    });

    nav.addEventListener("click", function (event) {
      if (event.target.closest("a")) setOpen(false);
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") setOpen(false);
    });

    if (window.matchMedia) {
      var desktop = window.matchMedia("(min-width: 821px)");
      var resetForDesktop = function (event) {
        if (event.matches) setOpen(false);
      };
      if (typeof desktop.addEventListener === "function") desktop.addEventListener("change", resetForDesktop);
      else if (typeof desktop.addListener === "function") desktop.addListener(resetForDesktop);
    }

    setOpen(false);
  }

  function selectedUploadBatchSize(control) {
    var value = parseInt(control && control.value, 10);
    return [20, 30, 40, 50].indexOf(value) !== -1 ? value : 20;
  }

  function buildUploadBatches(files, maxFiles, maxBytes) {
    var batches = [];
    var current = [];
    var currentBytes = 0;
    files.forEach(function (file) {
      var fileBytes = Math.max(0, Number(file.size) || 0);
      if (current.length && (current.length >= maxFiles || currentBytes + fileBytes > maxBytes)) {
        batches.push(current);
        current = [];
        currentBytes = 0;
      }
      current.push(file);
      currentBytes += fileBytes;
    });
    if (current.length) batches.push(current);
    return batches;
  }

  function renderFiles(input, target) {
    if (!input || !target) return;
    target.innerHTML = "";
    var files = Array.prototype.slice.call(input.files || []);
    input.setCustomValidity("");

    if (!files.length) return;

    var summary = document.createElement("span");
    summary.className = "gzca-file-summary";
    var batchSize = selectedUploadBatchSize(document.querySelector("[data-upload-batch-size]"));
    summary.innerHTML = "<b>已选择 " + files.length + " 张</b><small>每批最多 " + batchSize + " 张，且不超过 72 MB；系统会自动拆分并依次上传。</small>";
    target.appendChild(summary);

    files.slice(0, 80).forEach(function (file) {
      var row = document.createElement("span");
      var name = document.createElement("b");
      var size = document.createElement("small");
      name.textContent = file.name;
      size.textContent = formatSize(file.size);
      row.appendChild(name);
      row.appendChild(size);
      target.appendChild(row);
    });
    if (files.length > 80) {
      var more = document.createElement("span");
      more.className = "gzca-file-more";
      more.textContent = "还有 " + (files.length - 80) + " 张未展开显示，会继续参与上传。";
      target.appendChild(more);
    }
  }

  function initUpload() {
    var form = document.querySelector("[data-upload-form]");
    var input = document.querySelector("[data-file-input]");
    var list = document.querySelector("[data-file-list]");
    var dropzone = document.querySelector("[data-dropzone]");
    var category = document.querySelector("[data-upload-category]");
    var prefix = document.querySelector("[data-code-prefix]");
    var selection = document.querySelector("[data-upload-selection]");
    var path = document.querySelector("[data-upload-path]");
    var submit = document.querySelector("[data-upload-submit]");
    var progress = document.querySelector("[data-upload-progress]");
    var progressTitle = document.querySelector("[data-upload-progress-title]");
    var progressCount = document.querySelector("[data-upload-progress-count]");
    var progressBar = document.querySelector("[data-upload-progress-bar]");
    var progressLog = document.querySelector("[data-upload-progress-log]");
    var batchSizeControl = document.querySelector("[data-upload-batch-size]");
    var maxBatchBytes = 72 * 1024 * 1024;

    function addLog(text, type) {
      if (!progressLog) return;
      var row = document.createElement("span");
      if (type) row.className = "is-" + type;
      row.textContent = text;
      progressLog.prepend(row);
      while (progressLog.children.length > 80) {
        progressLog.removeChild(progressLog.lastChild);
      }
    }

    function setProgress(done, total, title) {
      if (progress) progress.hidden = false;
      if (progressTitle) progressTitle.textContent = title || "正在上传";
      if (progressCount) progressCount.textContent = done + " / " + total;
      if (progressBar) progressBar.value = total > 0 ? Math.round(done / total * 100) : 0;
    }

    if (input) input.addEventListener("change", function () { renderFiles(input, list); });
    if (batchSizeControl) batchSizeControl.addEventListener("change", function () { renderFiles(input, list); });

    if (dropzone && input) {
      ["dragenter", "dragover"].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
          event.preventDefault();
          dropzone.classList.add("is-dragging");
        });
      });
      ["dragleave", "drop"].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
          event.preventDefault();
          dropzone.classList.remove("is-dragging");
        });
      });
      dropzone.addEventListener("drop", function (event) {
        if (event.dataTransfer && event.dataTransfer.files.length) {
          input.files = event.dataTransfer.files;
          renderFiles(input, list);
        }
      });
    }

    if (category && prefix) {
      var syncCategory = function () {
        var option = category.options[category.selectedIndex];
        var nextPrefix = option ? option.getAttribute("data-prefix") : "";
        var nextPath = option ? option.getAttribute("data-path") : "";
        prefix.value = nextPrefix || "";
        if (selection && path) {
          selection.hidden = !nextPath;
          path.textContent = nextPath ? nextPath + "，编号前缀 " + nextPrefix : "";
        }
      };
      category.addEventListener("change", syncCategory);
      syncCategory();
    }

    if (form && input && window.fetch && window.FormData) {
      form.addEventListener("submit", function (event) {
        var files = Array.prototype.slice.call(input.files || []);
        if (!files.length) return;
        event.preventDefault();

        if (!category || !category.value) {
          showNotice("请选择上传板块", "先选择作品要归属的前台板块，再开始上传。");
          return;
        }

        var token = form.querySelector("input[name='pwg_token']");
        var publishNow = form.querySelector("input[name='publish_now'][value='1']");
        var setCover = form.querySelector("input[name='set_cover']");
        var oversized = files.filter(function (file) { return file.size > maxBatchBytes; });
        if (oversized.length) {
          showNotice("单张图片过大", "“" + oversized[0].name + "”超过 72 MB，请先压缩后再上传。");
          return;
        }
        var batchSize = selectedUploadBatchSize(batchSizeControl);
        var batches = buildUploadBatches(files, batchSize, maxBatchBytes);

        var uploaded = 0;
        var failed = 0;
        if (submit) {
          submit.disabled = true;
          submit.dataset.originalText = submit.textContent;
          submit.textContent = "正在分批上传…";
        }
        if (progressLog) progressLog.innerHTML = "";
        setProgress(0, files.length, "开始上传");
        addLog("共 " + files.length + " 张，分为 " + batches.length + " 批；每批最多 " + batchSize + " 张且不超过 72 MB。", "info");

        (async function () {
          for (var batchIndex = 0; batchIndex < batches.length; batchIndex++) {
            var batch = batches[batchIndex];
            var data = new FormData();
            if (token) data.append("pwg_token", token.value);
            data.append("gzca_action", "upload_works");
            data.append("gzca_async", "1");
            data.append("album_id", category.value);
            data.append("publish_now", publishNow && publishNow.checked ? "1" : "0");
            if (setCover && setCover.checked && batchIndex === 0) data.append("set_cover", "1");
            batch.forEach(function (file) { data.append("artworks[]", file, file.name); });

            setProgress(uploaded + failed, files.length, "正在上传第 " + (batchIndex + 1) + " / " + batches.length + " 批（" + batch.length + " 张）");
            try {
              var response = await fetch(form.action, {
                method: "POST",
                body: data,
                credentials: "same-origin",
                headers: { "X-Requested-With": "XMLHttpRequest" }
              });
              var payload = await response.json();
              uploaded += payload.uploaded || 0;
              if (payload.errors && payload.errors.length) {
                failed += Math.max(0, batch.length - (payload.uploaded || 0));
                payload.errors.forEach(function (message) { addLog(message, "error"); });
              }
              if (payload.message) addLog(payload.message, payload.ok ? "success" : "info");
            }
            catch (error) {
              failed += batch.length;
              addLog("第 " + (batchIndex + 1) + " 批上传失败：" + error.message, "error");
            }
            setProgress(uploaded + failed, files.length, "已处理 " + (uploaded + failed) + " 张");
          }

          if (submit) {
            submit.disabled = false;
            submit.textContent = submit.dataset.originalText || "开始上传作品";
          }
          setProgress(uploaded + failed, files.length, failed > 0 ? "上传完成，部分失败" : "上传完成");
          addLog("完成：成功 " + uploaded + " 张，失败 " + failed + " 张。", failed > 0 ? "error" : "success");
          if (failed === 0) {
            input.value = "";
            renderFiles(input, list);
          }
        })();
      });
    }
  }

  var confirmationLayer = null;
  var confirmationState = null;

  function ensureConfirmationLayer() {
    if (confirmationLayer) return confirmationLayer;

    confirmationLayer = document.createElement("div");
    confirmationLayer.className = "gzca-confirm-layer";
    confirmationLayer.hidden = true;
    confirmationLayer.setAttribute("data-gzca-confirm", "");
    confirmationLayer.innerHTML = [
      '<section class="gzca-confirm-dialog" role="region" aria-live="polite" aria-labelledby="gzca-confirm-title" aria-describedby="gzca-confirm-body">',
      '  <div class="gzca-confirm-topline">',
      '    <span data-confirm-step>操作确认</span>',
      '    <button class="gzca-confirm-close" type="button" aria-label="关闭确认面板" data-confirm-cancel>&times;</button>',
      '  </div>',
      '  <div class="gzca-confirm-heading">',
      '    <span class="gzca-confirm-mark" aria-hidden="true">!</span>',
      '    <div><p data-confirm-eyebrow>谨慎操作</p><h2 id="gzca-confirm-title" data-confirm-title></h2></div>',
      '  </div>',
      '  <p class="gzca-confirm-body" id="gzca-confirm-body" data-confirm-body></p>',
      '  <div class="gzca-confirm-detail" data-confirm-detail></div>',
      '  <div class="gzca-confirm-actions">',
      '    <button class="gzca-button gzca-button-quiet" type="button" data-confirm-cancel>取消</button>',
      '    <button class="gzca-button" type="button" data-confirm-accept>确认</button>',
      '  </div>',
      '</section>'
    ].join("");
    var host = document.querySelector(".gzca-main") || document.querySelector("#content") || document.body;
    var header = host.querySelector(".gzca-page-header");
    if (header && header.nextSibling) host.insertBefore(confirmationLayer, header.nextSibling);
    else host.appendChild(confirmationLayer);

    confirmationLayer.addEventListener("click", function (event) {
      if (event.target.closest("[data-confirm-cancel]")) {
        closeConfirmation(false);
        return;
      }
      if (event.target.closest("[data-confirm-accept]")) advanceConfirmation();
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && confirmationState) closeConfirmation(false);
    });

    return confirmationLayer;
  }

  function renderConfirmation() {
    if (!confirmationState) return;
    var layer = ensureConfirmationLayer();
    var step = confirmationState.steps[confirmationState.index];
    var accept = layer.querySelector("[data-confirm-accept]");
    var detail = layer.querySelector("[data-confirm-detail]");

    layer.querySelector("[data-confirm-step]").textContent = confirmationState.steps.length > 1
      ? "第 " + (confirmationState.index + 1) + " / " + confirmationState.steps.length + " 步"
      : "操作确认";
    layer.querySelector("[data-confirm-eyebrow]").textContent = step.eyebrow || "谨慎操作";
    layer.querySelector("[data-confirm-title]").textContent = step.title;
    layer.querySelector("[data-confirm-body]").textContent = step.body;
    detail.textContent = step.detail || "";
    detail.hidden = !step.detail;
    accept.textContent = step.confirmLabel || "确认";
    accept.classList.toggle("gzca-button-danger", step.tone === "danger");
    accept.classList.toggle("gzca-button-primary", step.tone !== "danger");
    layer.querySelector(".gzca-confirm-dialog").classList.toggle("is-danger", step.tone === "danger");
  }

  function openConfirmation(options) {
    ensureConfirmationLayer();
    if (confirmationState) closeConfirmation(false);

    return new Promise(function (resolve) {
      confirmationState = {
        index: 0,
        steps: options.steps,
        resolve: resolve,
        returnFocus: document.activeElement
      };
      confirmationLayer.hidden = false;
      renderConfirmation();
      confirmationLayer.scrollIntoView({ behavior: "smooth", block: "start" });
      window.setTimeout(function () {
        var cancel = confirmationLayer.querySelector(".gzca-confirm-actions [data-confirm-cancel]");
        if (cancel) cancel.focus();
      }, 0);
    });
  }

  function advanceConfirmation() {
    if (!confirmationState) return;
    if (confirmationState.index < confirmationState.steps.length - 1) {
      confirmationState.index += 1;
      renderConfirmation();
      var cancel = confirmationLayer.querySelector(".gzca-confirm-actions [data-confirm-cancel]");
      if (cancel) cancel.focus();
      return;
    }
    closeConfirmation(true);
  }

  function closeConfirmation(confirmed) {
    if (!confirmationState) return;
    var state = confirmationState;
    confirmationState = null;
    confirmationLayer.hidden = true;
    if (state.returnFocus && typeof state.returnFocus.focus === "function") state.returnFocus.focus();
    state.resolve(confirmed);
  }

  function showNotice(title, body) {
    return openConfirmation({ steps: [{
      eyebrow: "操作提示",
      title: title,
      body: body,
      detail: "请检查当前选择后再继续。",
      confirmLabel: "知道了",
      tone: "normal"
    }] });
  }

  function submitAfterConfirmation(form, button, confirmValue) {
    var confirmInput = form.querySelector("input[name='delete_confirm']");
    if (confirmInput && confirmValue) confirmInput.value = confirmValue;
    form.setAttribute("data-confirmed-submit", "1");
    if (typeof form.requestSubmit === "function") form.requestSubmit(button || undefined);
    else form.submit();
  }

  function initConfirmations() {
    document.querySelectorAll("form").forEach(function (form) {
      var action = form.querySelector("input[name='gzca_action']");
      if (!action) return;

      form.addEventListener("submit", function (event) {
        var button = form.querySelector("button[type='submit']");
        var options = null;
        var confirmValue = "";

        if (action.value === "download_work") return;

        if (form.getAttribute("data-confirmed-submit") === "1") {
          form.removeAttribute("data-confirmed-submit");
        }
        else if (action.value === "toggle_work") {
          var label = button ? button.textContent.trim() : "修改状态";
          options = { steps: [{
            eyebrow: "作品状态",
            title: "确认" + label + "这张作品？",
            body: "状态修改后会立即同步到网站前台。",
            detail: "此操作不会删除原图、缩略图或作品记录。",
            confirmLabel: "确认" + label,
            tone: "normal"
          }] };
        }
        else if (action.value === "unset_cover") {
          var coverTitle = button ? (button.getAttribute("title") || "") : "";
          var coverCategory = coverTitle.replace(/^下架\s*/, "").replace(/\s*的封面.*$/, "") || "这个板块";
          options = { steps: [{
            eyebrow: "封面下架",
            title: "下架「" + coverCategory + "」的当前封面？",
            body: "确认后，该板块前台会恢复默认背景或无封面状态。",
            detail: "作品本身仍保持原来的上架、公开和分类状态，不会删除图片；之后可以重新设置封面。",
            confirmLabel: "确认下架封面",
            tone: "normal"
          }] };
        }
        else if (action.value === "hide_category" || action.value === "show_category") {
          var categoryName = form.getAttribute("data-category-name") || "这个板块";
          var isHiding = action.value === "hide_category";
          options = { steps: [{
            eyebrow: "板块状态",
            title: (isHiding ? "隐藏" : "显示") + "「" + categoryName + "」？",
            body: isHiding ? "隐藏后，前台暂时不再显示这个板块。" : "显示后，这个板块会重新出现在网站前台。",
            detail: "图片文件、作品记录和分类关系都会保留，可随时恢复。",
            confirmLabel: "确认" + (isHiding ? "隐藏" : "显示"),
            tone: "normal"
          }] };
        }
        else if (action.value === "delete_category") {
          var deleteName = form.getAttribute("data-category-name") || "这个板块";
          confirmValue = "永久删除";
          options = { steps: [
            {
              eyebrow: "永久删除板块",
              title: "删除「" + deleteName + "」？",
              body: "将删除这个板块、它的全部子板块，以及只属于这些板块的作品。",
              detail: "相关原图、缩略图、缓存图和数据库记录会被永久清理；同时属于其他板块的共享图片不会误删。",
              confirmLabel: "继续核对",
              tone: "danger"
            },
            {
              eyebrow: "最后确认",
              title: "删除后无法恢复",
              body: "请再次核对板块名称和作品范围。确认后将立即执行物理删除并释放服务器空间。",
              detail: "如果只是暂时不在前台展示，请取消并使用“隐藏”功能。",
              confirmLabel: "永久删除",
              tone: "danger"
            }
          ] };
        }
        else if (action.value === "delete_work") {
          var workName = form.getAttribute("data-work-name") || "这张作品";
          confirmValue = "永久删除作品";
          options = { steps: [
            {
              eyebrow: "永久删除作品",
              title: "删除「" + workName + "」？",
              body: "将从服务器和数据库中永久移除这张作品。",
              detail: "原图、缩略图、缓存图、分类关系和作品记录都会一并清理。",
              confirmLabel: "继续核对",
              tone: "danger"
            },
            {
              eyebrow: "最后确认",
              title: "作品删除后无法恢复",
              body: "确认后将立即执行物理删除并释放服务器空间。",
              detail: "如果只是暂时不展示，请取消并使用“下架”功能。",
              confirmLabel: "永久删除",
              tone: "danger"
            }
          ] };
        }

        if (options) {
          event.preventDefault();
          openConfirmation(options).then(function (confirmed) {
            if (confirmed) submitAfterConfirmation(form, button, confirmValue);
          });
          return;
        }

        if (!event.defaultPrevented && button) {
          button.disabled = true;
          button.dataset.originalText = button.textContent;
          button.textContent = action.value === "upload_works" ? "正在上传…" : ((action.value === "delete_category" || action.value === "delete_work") ? "正在删除…" : "正在保存…");
        }
      });
    });
  }

  function initCompetitionForm() {
    var slug = document.querySelector("input[name='slug']");
    if (!slug) return;
    slug.addEventListener("input", function () {
      slug.value = slug.value.toLowerCase().replace(/[^a-z0-9-]/g, "");
    });
  }

  function initCategoryForm() {
    var form = document.querySelector("[data-category-form]");
    if (!form) return;
    var parent = form.querySelector("[data-category-parent]");
    var kind = form.querySelector("[data-category-kind]");
    var prefix = form.querySelector("input[name='code_prefix']");
    var directUpload = form.querySelector("input[name='direct_upload']");

    if (parent && kind) {
      parent.addEventListener("change", function () {
        var option = parent.options[parent.selectedIndex];
        if (option && option.getAttribute("data-kind") === "exhibition") kind.value = "exhibition";
        if (directUpload) directUpload.checked = Boolean(parent.value);
      });
    }
    if (prefix) {
      prefix.addEventListener("input", function () {
        prefix.value = prefix.value.toUpperCase().replace(/[^A-Z0-9-]/g, "");
      });
    }
  }

  function initCategoryBrowser() {
    var browser = document.querySelector("[data-category-browser]");
    if (!browser) return;

    var cards = Array.prototype.slice.call(browser.querySelectorAll("[data-category-card]"));
    var filter = browser.querySelector("[data-category-filter]");
    var perPage = browser.querySelector("[data-category-per-page]");
    var prevButtons = Array.prototype.slice.call(browser.querySelectorAll("[data-category-prev]"));
    var nextButtons = Array.prototype.slice.call(browser.querySelectorAll("[data-category-next]"));
    var summaries = Array.prototype.slice.call(browser.querySelectorAll("[data-category-page-summary]"));
    var labels = Array.prototype.slice.call(browser.querySelectorAll("[data-category-page-label]"));
    var empty = browser.querySelector("[data-category-empty]");
    var page = 1;

    function selectedValue() {
      return filter ? filter.value : "all";
    }

    function matches(card) {
      var value = selectedValue();
      if (value === "all") return true;
      if (value === "kind-catalog") return card.getAttribute("data-category-kind") !== "exhibition";
      if (value === "kind-exhibition") return card.getAttribute("data-category-kind") === "exhibition";
      if (value === "hidden") return card.getAttribute("data-category-hidden") === "1" || Boolean(card.querySelector(".gzca-status.is-offline"));
      if (value.indexOf("group-") === 0) return card.getAttribute("data-category-id") === value.replace("group-", "");
      return true;
    }

    function currentPerPage() {
      var value = perPage ? parseInt(perPage.value, 10) : 6;
      return value > 0 ? value : 6;
    }

    function update() {
      var filtered = cards.filter(matches);
      var limit = currentPerPage();
      var pages = Math.max(1, Math.ceil(filtered.length / limit));
      if (page > pages) page = pages;
      var start = (page - 1) * limit;
      var end = start + limit;
      var visible = filtered.slice(start, end);

      cards.forEach(function (card) { card.hidden = true; });
      visible.forEach(function (card) { card.hidden = false; });

      if (empty) empty.hidden = filtered.length > 0;
      summaries.forEach(function (summary) {
        summary.textContent = filtered.length > 0
          ? "显示 " + (start + 1) + "-" + Math.min(end, filtered.length) + " / 共 " + filtered.length + " 个板块组"
          : "没有匹配的板块";
      });
      labels.forEach(function (label) { label.textContent = "第 " + page + " / " + pages + " 页"; });
      prevButtons.forEach(function (button) { button.disabled = page <= 1; });
      nextButtons.forEach(function (button) { button.disabled = page >= pages; });
    }

    if (filter) filter.addEventListener("change", function () { page = 1; update(); });
    if (perPage) perPage.addEventListener("change", function () { page = 1; update(); });
    prevButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        if (page > 1) {
          page -= 1;
          update();
        }
      });
    });
    nextButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        page += 1;
        update();
      });
    });

    update();
  }

  function initWorkFilters() {
    var form = document.querySelector("[data-work-filter-form]");
    if (!form) return;
    form.querySelectorAll("[data-auto-filter]").forEach(function (select) {
      select.addEventListener("change", function () {
        if (typeof form.requestSubmit === "function") form.requestSubmit();
        else form.submit();
      });
    });
  }

  function initBulkWorks() {
    var form = document.querySelector("[data-bulk-form]");
    if (!form) return;
    var items = Array.prototype.slice.call(document.querySelectorAll("[data-bulk-item]"));
    var toggles = Array.prototype.slice.call(document.querySelectorAll("[data-bulk-select-all]"));
    var count = document.querySelector("[data-bulk-count]");
    var action = form.querySelector("[data-bulk-action]");
    var album = form.querySelector("select[name='bulk_album_id']");
    var bulkDeleteConfirm = form.querySelector("input[name='bulk_delete_confirm']");

    var selectedItems = function () {
      return items.filter(function (item) { return item.checked; });
    };

    var update = function () {
      var selected = selectedItems().length;
      if (count) count.textContent = "已选 " + selected + " 张";
      toggles.forEach(function (toggle) {
        toggle.checked = items.length > 0 && selected === items.length;
        toggle.indeterminate = selected > 0 && selected < items.length;
      });
    };

    toggles.forEach(function (toggle) {
      toggle.addEventListener("change", function () {
        items.forEach(function (item) { item.checked = toggle.checked; });
        update();
      });
    });

    items.forEach(function (item) {
      item.addEventListener("change", update);
    });

    form.addEventListener("submit", function (event) {
      if (form.getAttribute("data-bulk-confirmed") === "1") {
        form.removeAttribute("data-bulk-confirmed");
        return;
      }

      var selected = selectedItems().length;
      if (selected === 0) {
        event.preventDefault();
        showNotice("还没有选择作品", "请先勾选要批量处理的作品。");
        return;
      }
      var actionValue = action ? action.value : "";
      if (!actionValue) {
        event.preventDefault();
        showNotice("请选择批量操作", "选择上架、下架、移动或永久删除后再执行。");
        return;
      }
      if (actionValue === "move" && (!album || album.value === "0")) {
        event.preventDefault();
        showNotice("请选择目标板块", "批量移动前，需要先选择作品要归属的目标板块。");
        return;
      }
      if (bulkDeleteConfirm) bulkDeleteConfirm.value = "";

      var options = null;
      if (actionValue === "delete") {
        options = { steps: [
          {
            eyebrow: "批量永久删除",
            title: "删除选中的 " + selected + " 张作品？",
            body: "将从服务器和数据库中永久移除这些作品。",
            detail: "相关原图、缩略图、缓存图、分类关系和作品记录都会一并清理。",
            confirmLabel: "继续核对",
            tone: "danger"
          },
          {
            eyebrow: "最后确认",
            title: "批量删除后无法恢复",
            body: "确认后将立即执行物理删除并释放服务器空间。",
            detail: "如果只是暂时不展示，请取消并改用“批量下架”。",
            confirmLabel: "永久删除 " + selected + " 张",
            tone: "danger"
          }
        ] };
      }
      else {
        var label = action.options[action.selectedIndex] ? action.options[action.selectedIndex].textContent : "批量操作";
        var target = actionValue === "move" && album && album.options[album.selectedIndex] ? "到「" + album.options[album.selectedIndex].textContent.trim() + "」" : "";
        options = { steps: [{
          eyebrow: "批量操作",
          title: label + target + "？",
          body: "本次将处理选中的 " + selected + " 张作品。",
          detail: actionValue === "move" ? "只调整作品所属板块，不会删除图片文件。" : "操作完成后会立即同步到网站前台。",
          confirmLabel: "确认执行",
          tone: "normal"
        }] };
      }

      event.preventDefault();
      openConfirmation(options).then(function (confirmed) {
        if (!confirmed) return;
        if (actionValue === "delete" && bulkDeleteConfirm) bulkDeleteConfirm.value = "永久删除作品";
        form.setAttribute("data-bulk-confirmed", "1");
        if (typeof form.requestSubmit === "function") form.requestSubmit();
        else form.submit();
      });
    });

    update();
  }

  document.addEventListener("DOMContentLoaded", function () {
    initMobileNavigation();
    initUpload();
    initBulkWorks();
    initConfirmations();
    initCompetitionForm();
    initCategoryForm();
    initCategoryBrowser();
    initWorkFilters();
  });
})();

document.addEventListener("DOMContentLoaded", function () {
  const toggleInput = document.getElementById("dds-toggle");

  const toggleDragAndDrop = (state) => {
    toggleInput.checked = !!state;

    if (state) {
      jQuery(".ui-sortable").each(function() {
        let parentContainerByClass = jQuery(this).parent(".postbox-container");
        let parentContainerById = jQuery(this).parent("#post-body-content");

        if (parentContainerByClass.length || parentContainerById.length) {
          jQuery(this).sortable("disable");
          jQuery(this).find(".hndle").css("cursor", "default");
        }
      });
      jQuery(".handle-order-lower").hide();
      jQuery(".handle-order-higher").hide();
    } else {
      jQuery(".ui-sortable").sortable("enable");
      jQuery(".hndle").css("cursor", "move");
      jQuery(".handle-order-lower").show();
      jQuery(".handle-order-higher").show();
    }
  };

  toggleDragAndDrop(DragDropSwitch.disabled === 'true');

  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      mutation.addedNodes.forEach(function(node) {
        if (node.nodeType === 1 && jQuery(node).hasClass("ui-sortable")) {
          toggleDragAndDrop(toggleInput.checked);
        }
      });

      if (mutation.type === "attributes" && mutation.attributeName === "class") {
        if (jQuery(mutation.target).hasClass("ui-sortable")) {
          toggleDragAndDrop(toggleInput.checked);
        }
      }
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ["class"]
  });

  toggleInput.addEventListener("change", function () {
    const disableDrag = toggleInput.checked;

    toggleDragAndDrop(disableDrag);

    fetch(ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "dds_update_user_meta",
        dds_state: disableDrag ? "1" : "0",
        dds_nonce: DragDropSwitch.nonce,
      }),
    })
      .then((response) => response.json())
      .then(() => {});
  });
});

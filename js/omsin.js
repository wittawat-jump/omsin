function initEditInplace(id, className) {
  var patt = new RegExp(id + "_(name)_([0-9_]+)"),
    o = {
      onSave: function(v) {
        var req = new GAjax({
          asynchronous: false
        });
        req.initLoading(this, false);
        req.send(
          "index.php/" + className,
          "action=" + this.id + "&value=" + encodeURIComponent(v)
        );
        req.hideLoading();
        var ds = req.responseText.toJSON();
        if (ds) {
          if (ds.alert) {
            alert(ds.alert);
          }
          if (ds.edit) {
            $E(ds.editId).innerHTML = ds.edit;
          }
          return true;
        } else if (req.responseText != "") {
          alert(req.responseText);
        }
        return false;
      }
    };
  function _doInitEditInplaceMethod(id) {
    var loading = true;
    forEach($G(id).elems("*"), function() {
      var hs = patt.exec(this.id);
      if (hs) {
        if (hs[1] == "name") {
          editor = new EditInPlace(this, o);
        }
      }
    });
    loading = false;
  }
  _doInitEditInplaceMethod(id);
}
function initIerecord() {
  var doStatusChanged = function() {
    if (this.value == "TRANSFER") {
      $G("transfer").removeClass("hidden");
      $E("write_category").parentNode.parentNode.style.display = "none";
      $E("write_wallet").parentNode.parentNode.style.display = "none";
      $E("write_wallet_name").parentNode.parentNode.style.display = "none";
      $E("write_comment").parentNode.parentNode.style.display = "none";
    } else if (this.value == "INIT") {
      $G("transfer").addClass("hidden");
      $E("write_category").parentNode.parentNode.style.display = "none";
      $E("write_wallet").parentNode.parentNode.style.display = "none";
      $E("write_wallet_name").parentNode.parentNode.style.display = "block";
      $E("write_comment").parentNode.parentNode.style.display = "none";
      $E("write_wallet_name").focus();
    } else {
      $G("transfer").addClass("hidden");
      $E("write_category").parentNode.parentNode.style.display = "block";
      $E("write_wallet").parentNode.parentNode.style.display = "block";
      $G("write_category")
        .setValue("")
        .reset()
        .focus();
      $E("write_wallet_name").parentNode.parentNode.style.display = "none";
      $E("write_comment").parentNode.parentNode.style.display = "block";
    }
  };
  $G("write_status").addEvent("change", doStatusChanged);
  findCategory("write_category", "write_account_id", function() {
    return $E("write_status").value == "IN" ? 1 : 2;
  });
  findCategory("write_wallet", "write_account_id", function() {
    return 4;
  });
  doStatusChanged.call($E("write_status"));
}
function findCategory(name, account_id, typ, count) {
  var SearchGet = function() {
    q = "name=" + encodeURIComponent($E(name).value);
    q += "&id=" + $E(account_id).value;
    q += "&typ=" + typ.call();
    q += "&count=" + (count || 100);
    return q;
  };
  function SearchCallback() {
    $E(name).value = this.name.unentityify();
    $G(name).replaceClass("invalid", "valid");
  }
  function SearchPopulate() {
    var patt = new RegExp("(" + $E(name).value + ")", "gi");
    return (
      "<p><span>" +
      this.name.unentityify().replace(patt, "<em>$1</em>") +
      "</span></p>"
    );
  }
  function SearchRequest(datas) {
    $G(name).reset();
  }
  new GAutoComplete(name, {
    className: "gautocomplete",
    get: SearchGet,
    url: "xhr.php/index/model/autocomplete/findCategory",
    callBack: SearchCallback,
    populate: SearchPopulate,
    onRequest: SearchRequest
  });
}
function initModal(id, callback) {
  var patt = /^modal_([a-z]+)_(.*)$/;
  forEach($G(id).elems("a"), function() {
    if (patt.test(this.id)) {
      callClick(this, function() {
        showModal(
          "index.php/index/controller/modal",
          "data=" + this.id,
          callback || $K.emptyFunction
        );
      });
    }
  });
}
var doDatabaseReset = function() {
  if (confirm(CONFIRM_RESET_DATABASE)) {
    if (confirm(CONFIRM_RESET_DATABASE_B)) {
      send(
        WEB_URL + "xhr.php/index/model/database/action",
        "action=reset",
        doFormSubmit
      );
    }
  }
};

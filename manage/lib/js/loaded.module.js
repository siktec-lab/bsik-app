export const page = location;
export let module = {
    name : "",
    sub  : "",
    data : {}
};
export function decode(base64) {
    let valid = false;
    let obj = {};
    try {
        obj = JSON.parse(atob(base64));
        valid = true;
    } catch (e) {
        console.warn("something went wrong while loading module data", e);
    }
    return valid ? obj : {};
}
export function load() {
    let self = this;
    self.module.name = $("meta[name='module']").attr("content");
    self.module.sub  = $("meta[name='module-sub']").attr("content");
    $("meta[name='module-data']").each(function() {
        $.extend(self.module.data, self.decode($(this).attr("content")));
    });
}
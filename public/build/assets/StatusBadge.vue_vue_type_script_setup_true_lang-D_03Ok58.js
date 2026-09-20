import{_ as k}from"./index-CsF7OBHe.js";import{c as a}from"./createLucideIcon-DkD2-qZ4.js";import{C as n}from"./AppLayout.vue_vue_type_script_setup_true_lang-DjirQ6Nb.js";import{d as u,c as y,j as t,o as i,e as p,h as m,k as d,t as h,u as v}from"./app-DgabyM0n.js";/**
 * @license lucide-vue-next v0.468.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const g=a("BanIcon",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m4.9 4.9 14.2 14.2",key:"1m5liu"}]]);/**
 * @license lucide-vue-next v0.468.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const l=a("CircleDollarSignIcon",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8",key:"1h4pet"}],["path",{d:"M12 18V6",key:"zqpxq5"}]]);/**
 * @license lucide-vue-next v0.468.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const s=a("ClockIcon",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["polyline",{points:"12 6 12 12 16 14",key:"68esgv"}]]);/**
 * @license lucide-vue-next v0.468.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=a("PackageCheckIcon",[["path",{d:"m16 16 2 2 4-4",key:"gfu2re"}],["path",{d:"M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14",key:"e7tb2h"}],["path",{d:"m7.5 4.27 9 5.15",key:"1c824w"}],["polyline",{points:"3.29 7 12 12 20.71 7",key:"ousv84"}],["line",{x1:"12",x2:"12",y1:"22",y2:"12",key:"a4e8g8"}]]);/**
 * @license lucide-vue-next v0.468.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const b=a("TruckIcon",[["path",{d:"M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2",key:"wrbu53"}],["path",{d:"M15 18H9",key:"1lyqi6"}],["path",{d:"M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14",key:"lysw3i"}],["circle",{cx:"17",cy:"18",r:"2",key:"332jqn"}],["circle",{cx:"7",cy:"18",r:"2",key:"19iecd"}]]),E=u({__name:"StatusBadge",props:{status:{}},setup(r){const c=r,o={UNPAID:{label:"Belum Lunas",variant:"warning",icon:l},PARTIAL:{label:"Sebagian",variant:"warning",icon:l},PAID:{label:"Lunas",variant:"success",icon:n},PENDING:{label:"Menunggu",variant:"muted",icon:s},SHIPPED:{label:"Dikirim",variant:"default",icon:b},DELIVERED:{label:"Terkirim",variant:"success",icon:C},ACTIVE:{label:"Aktif",variant:"success",icon:n},CANCELLED:{label:"Dibatalkan",variant:"danger",icon:g}},e=y(()=>o[c.status??""]??{label:c.status??"-",variant:"muted",icon:s});return(D,I)=>(i(),t(v(k),{variant:e.value.variant},{default:p(()=>[(i(),t(d(e.value.icon),{class:"size-3.5"})),m(" "+h(e.value.label),1)]),_:1},8,["variant"]))}});export{g as B,E as _};

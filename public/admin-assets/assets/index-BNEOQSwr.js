import{d as r,R as t,f as i,j as e,h as n,S as h,i as m,k as p,l as u}from"./index-C9Bo83jw.js";const f=()=>{const{t:a}=r(),[s,l]=t.useState(!1),[o,c]=t.useState({}),d={type:"button",label:a("amis_editor.get_php_code"),level:"success",actionType:"ajax",api:{method:"post",url:"/dev_tools/editor_parse",data:{schema:o}},feedback:{title:"PHP Schema",size:"lg",body:{type:"editor",language:"php",name:"schema"}}};return i("div",{className:"h-screen",children:[e(n,{className:"h-full",title:a("amis_editor.editor"),bodyStyle:{padding:0,height:"calc(100% - 55px)"},extra:i(h,{children:[e(m,{schema:d}),e(p,{level:"primary",onClick:()=>l(!s),children:a(s?"amis_editor.edit":"amis_editor.preview")})]}),children:e("div",{className:"w-full h-full overflow-x-auto",children:e(u,{onChange:c,preview:s})})}),e("div",{className:"h-5"})]})};export{f as default};

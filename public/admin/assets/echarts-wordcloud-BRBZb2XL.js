import{V as Rt,p as Lt}from"./index-CWQ_ev-Y.js";import{h as Bt}from"./index-D0zDG80U.js";function Xt(J,rt){for(var $=0;$<rt.length;$++){const Y=rt[$];if(typeof Y!="string"&&!Array.isArray(Y)){for(const R in Y)if(R!=="default"&&!(R in J)){const j=Object.getOwnPropertyDescriptor(Y,R);j&&Object.defineProperty(J,R,j.get?j:{enumerable:!0,get:()=>Y[R]})}}}return Object.freeze(Object.defineProperty(J,Symbol.toStringTag,{value:"Module"}))}var Tt={exports:{}};const Yt=Rt(Bt);(function(J,rt){(function(Y,R){J.exports=R(Yt)})(self,function($){return(()=>{var Y={"./index.js":(L,K,mt)=>{mt.r(K);var O=mt("echarts/lib/echarts");O.extendSeriesModel({type:"series.wordCloud",visualStyleAccessPath:"textStyle",visualStyleMapper:function(f){return{fill:f.get("color")}},visualDrawType:"fill",optionUpdated:function(){var f=this.option;f.gridSize=Math.max(Math.floor(f.gridSize),4)},getInitialData:function(f,o){var a=O.helper.createDimensions(f.data,{coordDimensions:["value"]}),l=new O.List(a,this);return l.initData(f.data),l},defaultOption:{maskImage:null,shape:"circle",keepAspect:!1,left:"center",top:"center",width:"70%",height:"80%",sizeRange:[12,60],rotationRange:[-90,90],rotationStep:45,gridSize:8,drawOutOfBound:!1,shrinkToFit:!1,textStyle:{fontWeight:"normal"}}}),O.extendChartView({type:"wordCloud",render:function(f,o,a){var l=this.group;l.removeAll();var t=f.getData(),x=f.get("gridSize");f.layoutInstance.ondraw=function(d,r,T,P){var B=t.getItemModel(T),q=B.getModel("textStyle"),b=new O.graphic.Text({style:O.helper.createTextStyle(q),scaleX:1/P.info.mu,scaleY:1/P.info.mu,x:(P.gx+P.info.gw/2)*x,y:(P.gy+P.info.gh/2)*x,rotation:P.rot});b.setStyle({x:P.info.fillTextOffsetX,y:P.info.fillTextOffsetY+r*.5,text:d,verticalAlign:"middle",fill:t.getItemVisual(T,"style").fill,fontSize:r}),l.add(b),t.setItemGraphicEl(T,b),b.ensureState("emphasis").style=O.helper.createTextStyle(B.getModel(["emphasis","textStyle"]),{state:"emphasis"}),b.ensureState("blur").style=O.helper.createTextStyle(B.getModel(["blur","textStyle"]),{state:"blur"}),O.helper.enableHoverEmphasis(b,B.get(["emphasis","focus"]),B.get(["emphasis","blurScope"])),b.stateTransition={duration:f.get("animation")?f.get(["stateAnimation","duration"]):0,easing:f.get(["stateAnimation","easing"])},b.__highDownDispatcher=!0},this._model=f},remove:function(){this.group.removeAll(),this._model.layoutInstance.dispose()},dispose:function(){this._model.layoutInstance.dispose()}});/*!
 * wordcloud2.js
 * http://timdream.org/wordcloud2.js/
 *
 * Copyright 2011 - 2019 Tim Guan-tin Chien and contributors.
 * Released under the MIT license
 */window.setImmediate||(window.setImmediate=function(){return window.msSetImmediate||window.webkitSetImmediate||window.mozSetImmediate||window.oSetImmediate||function(){if(!window.postMessage||!window.addEventListener)return null;var a=[void 0],l="zero-timeout-message",t=function(d){var r=a.length;return a.push(d),window.postMessage(l+r.toString(36),"*"),r};return window.addEventListener("message",function(d){if(!(typeof d.data!="string"||d.data.substr(0,l.length)!==l)){d.stopImmediatePropagation();var r=parseInt(d.data.substr(l.length),36);a[r]&&(a[r](),a[r]=void 0)}},!0),window.clearImmediate=function(d){a[d]&&(a[d]=void 0)},t}()||function(a){window.setTimeout(a,0)}}()),window.clearImmediate||(window.clearImmediate=function(){return window.msClearImmediate||window.webkitClearImmediate||window.mozClearImmediate||window.oClearImmediate||function(a){window.clearTimeout(a)}}());var it=function(){var o=document.createElement("canvas");if(!o||!o.getContext)return!1;var a=o.getContext("2d");return!(!a||!a.getImageData||!a.fillText||!Array.prototype.some||!Array.prototype.push)}(),nt=function(){if(it){for(var o=document.createElement("canvas").getContext("2d"),a=20,l,t;a;){if(o.font=a.toString(10)+"px sans-serif",o.measureText("Ｗ").width===l&&o.measureText("m").width===t)return a+1;l=o.measureText("Ｗ").width,t=o.measureText("m").width,a--}return 0}}(),bt=function(f){if(Array.isArray(f)){var o=f.slice();return o.splice(0,2),o}else return[]},It=function(o){for(var a,l,t=o.length;t;)a=Math.floor(Math.random()*t),l=o[--t],o[t]=o[a],o[a]=l;return o},U={},ot=function(o,a){if(!it)return;var l=Math.floor(Math.random()*Date.now());Array.isArray(o)||(o=[o]),o.forEach(function(c,e){if(typeof c=="string"){if(o[e]=document.getElementById(c),!o[e])throw new Error("The element id specified is not found.")}else if(!c.tagName&&!c.appendChild)throw new Error("You must pass valid HTML elements, or ID of the element.")});var t={list:[],fontFamily:'"Trebuchet MS", "Heiti TC", "微軟正黑體", "Arial Unicode MS", "Droid Fallback Sans", sans-serif',fontWeight:"normal",color:"random-dark",minSize:0,weightFactor:1,clearCanvas:!0,backgroundColor:"#fff",gridSize:8,drawOutOfBound:!1,shrinkToFit:!1,origin:null,drawMask:!1,maskColor:"rgba(255,0,0,0.3)",maskGapWidth:.3,layoutAnimation:!0,wait:0,abortThreshold:0,abort:function(){},minRotation:-Math.PI/2,maxRotation:Math.PI/2,rotationStep:.1,shuffle:!0,rotateRatio:.1,shape:"circle",ellipticity:.65,classes:null,hover:null,click:null};if(a)for(var x in a)x in t&&(t[x]=a[x]);if(typeof t.weightFactor!="function"){var d=t.weightFactor;t.weightFactor=function(e){return e*d}}if(typeof t.shape!="function")switch(t.shape){case"circle":default:t.shape="circle";break;case"cardioid":t.shape=function(e){return 1-Math.sin(e)};break;case"diamond":t.shape=function(e){var i=e%(2*Math.PI/4);return 1/(Math.cos(i)+Math.sin(i))};break;case"square":t.shape=function(e){return Math.min(1/Math.abs(Math.cos(e)),1/Math.abs(Math.sin(e)))};break;case"triangle-forward":t.shape=function(e){var i=e%(2*Math.PI/3);return 1/(Math.cos(i)+Math.sqrt(3)*Math.sin(i))};break;case"triangle":case"triangle-upright":t.shape=function(e){var i=(e+Math.PI*3/2)%(2*Math.PI/3);return 1/(Math.cos(i)+Math.sqrt(3)*Math.sin(i))};break;case"pentagon":t.shape=function(e){var i=(e+.955)%(2*Math.PI/5);return 1/(Math.cos(i)+.726543*Math.sin(i))};break;case"star":t.shape=function(e){var i=(e+.955)%(2*Math.PI/10);return(e+.955)%(2*Math.PI/5)-2*Math.PI/10>=0?1/(Math.cos(2*Math.PI/10-i)+3.07768*Math.sin(2*Math.PI/10-i)):1/(Math.cos(i)+3.07768*Math.sin(i))};break}t.gridSize=Math.max(Math.floor(t.gridSize),4);var r=t.gridSize,T=r-t.maskGapWidth,P=Math.abs(t.maxRotation-t.minRotation),B=Math.min(t.maxRotation,t.minRotation),q=t.rotationStep,b,I,E,G,F,_,N;function pt(c,e){return"hsl("+(Math.random()*360).toFixed()+","+(Math.random()*30+70).toFixed()+"%,"+(Math.random()*(e-c)+c).toFixed()+"%)"}switch(t.color){case"random-dark":N=function(){return pt(10,50)};break;case"random-light":N=function(){return pt(50,90)};break;default:typeof t.color=="function"&&(N=t.color);break}var Q;typeof t.fontWeight=="function"&&(Q=t.fontWeight);var lt=null;typeof t.classes=="function"&&(lt=t.classes);var st=!1,et=[],ft,xt=function(e){var i=e.currentTarget,n=i.getBoundingClientRect(),u,s;e.touches?(u=e.touches[0].clientX,s=e.touches[0].clientY):(u=e.clientX,s=e.clientY);var h=u-n.left,S=s-n.top,g=Math.floor(h*(i.width/n.width||1)/r),m=Math.floor(S*(i.height/n.height||1)/r);return et[g]?et[g][m]:null},yt=function(e){var i=xt(e);if(ft!==i){if(ft=i,!i){t.hover(void 0,void 0,e);return}t.hover(i.item,i.dimension,e)}},ut=function(e){var i=xt(e);i&&(t.click(i.item,i.dimension,e),e.preventDefault())},dt=[],Et=function(e){if(dt[e])return dt[e];var i=e*8,n=i,u=[];for(e===0&&u.push([G[0],G[1],0]);n--;){var s=1;t.shape!=="circle"&&(s=t.shape(n/i*2*Math.PI)),u.push([G[0]+e*s*Math.cos(-n/i*2*Math.PI),G[1]+e*s*Math.sin(-n/i*2*Math.PI)*t.ellipticity,n/i*2*Math.PI])}return dt[e]=u,u},ht=function(){return t.abortThreshold>0&&new Date().getTime()-_>t.abortThreshold},Ft=function(){return t.rotateRatio===0||Math.random()>t.rotateRatio?0:P===0?B:B+Math.round(Math.random()*P/q)*q},At=function(e,i,n,u){var s=t.weightFactor(i);if(s<=t.minSize)return!1;var h=1;s<nt&&(h=function(){for(var gt=2;gt*s<nt;)gt+=2;return gt}());var S;Q?S=Q(e,i,s,u):S=t.fontWeight;var g=document.createElement("canvas"),m=g.getContext("2d",{willReadFrequently:!0});m.font=S+" "+(s*h).toString(10)+"px "+t.fontFamily;var A=m.measureText(e).width/h,w=Math.max(s*h,m.measureText("m").width,m.measureText("Ｗ").width)/h,p=A+w*2,k=w*3,W=Math.ceil(p/r),D=Math.ceil(k/r);p=W*r,k=D*r;var M=-A/2,v=-w*.4,y=Math.ceil((p*Math.abs(Math.sin(n))+k*Math.abs(Math.cos(n)))/r),C=Math.ceil((p*Math.abs(Math.cos(n))+k*Math.abs(Math.sin(n)))/r),z=C*r,V=y*r;g.setAttribute("width",z),g.setAttribute("height",V),m.scale(1/h,1/h),m.translate(z*h/2,V*h/2),m.rotate(-n),m.font=S+" "+(s*h).toString(10)+"px "+t.fontFamily,m.fillStyle="#000",m.textBaseline="middle",m.fillText(e,M*h,(v+s*.5)*h);var at=m.getImageData(0,0,z,V).data;if(ht())return!1;for(var St=[],Z=C,H,ct,vt,X=[y/2,C/2,y/2,C/2];Z--;)for(H=y;H--;){vt=r;t:for(;vt--;)for(ct=r;ct--;)if(at[((H*r+vt)*z+(Z*r+ct))*4+3]){St.push([Z,H]),Z<X[3]&&(X[3]=Z),Z>X[1]&&(X[1]=Z),H<X[0]&&(X[0]=H),H>X[2]&&(X[2]=H);break t}}return{mu:h,occupied:St,bounds:X,gw:C,gh:y,fillTextOffsetX:M,fillTextOffsetY:v,fillTextWidth:A,fillTextHeight:w,fontSize:s}},Ot=function(e,i,n,u,s){for(var h=s.length;h--;){var S=e+s[h][0],g=i+s[h][1];if(S>=I||g>=E||S<0||g<0){if(!t.drawOutOfBound)return!1;continue}if(!b[S][g])return!1}return!0},Pt=function(e,i,n,u,s,h,S,g,m,A){var w=n.fontSize,p;N?p=N(u,s,w,h,S,A):p=t.color;var k;Q?k=Q(u,s,w,A):k=t.fontWeight;var W;lt?W=lt(u,s,w,A):W=t.classes,o.forEach(function(D){if(D.getContext){var M=D.getContext("2d"),v=n.mu;M.save(),M.scale(1/v,1/v),M.font=k+" "+(w*v).toString(10)+"px "+t.fontFamily,M.fillStyle=p,M.translate((e+n.gw/2)*r*v,(i+n.gh/2)*r*v),g!==0&&M.rotate(-g),M.textBaseline="middle",M.fillText(u,n.fillTextOffsetX*v,(n.fillTextOffsetY+w*.5)*v),M.restore()}else{var y=document.createElement("span"),C="";C="rotate("+-g/Math.PI*180+"deg) ",n.mu!==1&&(C+="translateX(-"+n.fillTextWidth/4+"px) scale("+1/n.mu+")");var z={position:"absolute",display:"block",font:k+" "+w*n.mu+"px "+t.fontFamily,left:(e+n.gw/2)*r+n.fillTextOffsetX+"px",top:(i+n.gh/2)*r+n.fillTextOffsetY+"px",width:n.fillTextWidth+"px",height:n.fillTextHeight+"px",lineHeight:w+"px",whiteSpace:"nowrap",transform:C,webkitTransform:C,msTransform:C,transformOrigin:"50% 40%",webkitTransformOrigin:"50% 40%",msTransformOrigin:"50% 40%"};p&&(z.color=p),y.textContent=u;for(var V in z)y.style[V]=z[V];if(m)for(var at in m)y.setAttribute(at,m[at]);W&&(y.className+=W),D.appendChild(y)}})},Wt=function(e,i,n,u,s){if(!(e>=I||i>=E||e<0||i<0)){if(b[e][i]=!1,n){var h=o[0].getContext("2d");h.fillRect(e*r,i*r,T,T)}st&&(et[e][i]={item:s,dimension:u})}},Dt=function(e,i,n,u,s,h){var S=s.occupied,g=t.drawMask,m;g&&(m=o[0].getContext("2d"),m.save(),m.fillStyle=t.maskColor);var A;if(st){var w=s.bounds;A={x:(e+w[3])*r,y:(i+w[0])*r,w:(w[1]-w[3]+1)*r,h:(w[2]-w[0]+1)*r}}for(var p=S.length;p--;){var k=e+S[p][0],W=i+S[p][1];k>=I||W>=E||k<0||W<0||Wt(k,W,g,A,h)}g&&m.restore()},_t=function c(e,i){if(i>20)return null;var n,u,s;Array.isArray(e)?(n=e[0],u=e[1]):(n=e.word,u=e.weight,s=e.attributes);var h=Ft(),S=bt(e),g=At(n,u,h,S);if(!g||ht())return!1;if(!t.drawOutOfBound&&!t.shrinkToFit){var m=g.bounds;if(m[1]-m[3]+1>I||m[2]-m[0]+1>E)return!1}for(var A=F+1,w=function(D){var M=Math.floor(D[0]-g.gw/2),v=Math.floor(D[1]-g.gh/2),y=g.gw,C=g.gh;return Ot(M,v,y,C,g.occupied)?(Pt(M,v,g,n,u,F-A,D[2],h,s,S),Dt(M,v,y,C,g,e),{gx:M,gy:v,rot:h,info:g}):!1};A--;){var p=Et(F-A);t.shuffle&&(p=[].concat(p),It(p));for(var k=0;k<p.length;k++){var W=w(p[k]);if(W)return W}}return t.shrinkToFit?(Array.isArray(e)?e[1]=e[1]*3/4:e.weight=e.weight*3/4,c(e,i+1)):null},tt=function(e,i,n){if(i)return!o.some(function(u){var s=new CustomEvent(e,{detail:n||{}});return!u.dispatchEvent(s)},this);o.forEach(function(u){var s=new CustomEvent(e,{detail:n||{}});u.dispatchEvent(s)},this)},zt=function(){var e=o[0];if(e.getContext)I=Math.ceil(e.width/r),E=Math.ceil(e.height/r);else{var i=e.getBoundingClientRect();I=Math.ceil(i.width/r),E=Math.ceil(i.height/r)}if(tt("wordcloudstart",!0)){G=t.origin?[t.origin[0]/r,t.origin[1]/r]:[I/2,E/2],F=Math.floor(Math.sqrt(I*I+E*E)),b=[];var n,u,s;if(!e.getContext||t.clearCanvas)for(o.forEach(function(v){if(v.getContext){var y=v.getContext("2d");y.fillStyle=t.backgroundColor,y.clearRect(0,0,I*(r+1),E*(r+1)),y.fillRect(0,0,I*(r+1),E*(r+1))}else v.textContent="",v.style.backgroundColor=t.backgroundColor,v.style.position="relative"}),n=I;n--;)for(b[n]=[],u=E;u--;)b[n][u]=!0;else{var h=document.createElement("canvas").getContext("2d");h.fillStyle=t.backgroundColor,h.fillRect(0,0,1,1);var S=h.getImageData(0,0,1,1).data,g=e.getContext("2d").getImageData(0,0,I*r,E*r).data;n=I;for(var m,A;n--;)for(b[n]=[],u=E;u--;){A=r;t:for(;A--;)for(m=r;m--;)for(s=4;s--;)if(g[((u*r+A)*I*r+(n*r+m))*4+s]!==S[s]){b[n][u]=!1;break t}b[n][u]!==!1&&(b[n][u]=!0)}g=h=S=void 0}if(t.hover||t.click){for(st=!0,n=I+1;n--;)et[n]=[];t.hover&&e.addEventListener("mousemove",yt),t.click&&(e.addEventListener("click",ut),e.addEventListener("touchstart",ut),e.addEventListener("touchend",function(v){v.preventDefault()}),e.style.webkitTapHighlightColor="rgba(0, 0, 0, 0)"),e.addEventListener("wordcloudstart",function v(){e.removeEventListener("wordcloudstart",v),e.removeEventListener("mousemove",yt),e.removeEventListener("click",ut),ft=void 0})}s=0;var w,p,k=!0;t.layoutAnimation?t.wait!==0?(w=window.setTimeout,p=window.clearTimeout):(w=window.setImmediate,p=window.clearImmediate):(w=function(v){v()},p=function(){k=!1});var W=function(y,C){o.forEach(function(z){z.addEventListener(y,C)},this)},D=function(y,C){o.forEach(function(z){z.removeEventListener(y,C)},this)},M=function v(){D("wordcloudstart",v),p(U[l])};W("wordcloudstart",M),U[l]=(t.layoutAnimation?w:setTimeout)(function v(){if(k){if(s>=t.list.length){p(U[l]),tt("wordcloudstop",!1),D("wordcloudstart",M),delete U[l];return}_=new Date().getTime();var y=_t(t.list[s],0),C=!tt("wordclouddrawn",!0,{item:t.list[s],drawn:y});if(ht()||C){p(U[l]),t.abort(),tt("wordcloudabort",!1),tt("wordcloudstop",!1),D("wordcloudstart",M);return}s++,U[l]=w(v,t.wait)}},t.wait)}};zt()};ot.isSupported=it,ot.minFontSize=nt;const wt=ot;if(!wt.isSupported)throw new Error("Sorry your browser not support wordCloud");function kt(f){for(var o=f.getContext("2d"),a=o.getImageData(0,0,f.width,f.height),l=o.createImageData(a),t=0,x=0,d=0;d<a.data.length;d+=4){var r=a.data[d+3];if(r>128){var T=a.data[d]+a.data[d+1]+a.data[d+2];t+=T,++x}}for(var P=t/x,d=0;d<a.data.length;d+=4){var T=a.data[d]+a.data[d+1]+a.data[d+2],r=a.data[d+3];r<128||T>P?(l.data[d]=0,l.data[d+1]=0,l.data[d+2]=0,l.data[d+3]=0):(l.data[d]=255,l.data[d+1]=255,l.data[d+2]=255,l.data[d+3]=255)}o.putImageData(l,0,0)}O.registerLayout(function(f,o){f.eachSeriesByType("wordCloud",function(a){var l=O.helper.getLayoutRect(a.getBoxLayoutParams(),{width:o.getWidth(),height:o.getHeight()}),t=a.get("keepAspect"),x=a.get("maskImage"),d=x?x.width/x.height:1;t&&Ct(l,d);var r=a.getData(),T=document.createElement("canvas");T.width=l.width,T.height=l.height;var P=T.getContext("2d");if(x)try{P.drawImage(x,0,0,T.width,T.height),kt(T)}catch(F){console.error("Invalid mask image"),console.error(F.toString())}var B=a.get("sizeRange"),q=a.get("rotationRange"),b=r.getDataExtent("value"),I=Math.PI/180,E=a.get("gridSize");wt(T,{list:r.mapArray("value",function(F,_){var N=r.getItemModel(_);return[r.getName(_),N.get("textStyle.fontSize",!0)||O.number.linearMap(F,b,B),_]}).sort(function(F,_){return _[1]-F[1]}),fontFamily:a.get("textStyle.fontFamily")||a.get("emphasis.textStyle.fontFamily")||f.get("textStyle.fontFamily"),fontWeight:a.get("textStyle.fontWeight")||a.get("emphasis.textStyle.fontWeight")||f.get("textStyle.fontWeight"),gridSize:E,ellipticity:l.height/l.width,minRotation:q[0]*I,maxRotation:q[1]*I,clearCanvas:!x,rotateRatio:1,rotationStep:a.get("rotationStep")*I,drawOutOfBound:a.get("drawOutOfBound"),shrinkToFit:a.get("shrinkToFit"),layoutAnimation:a.get("layoutAnimation"),shuffle:!1,shape:a.get("shape")});function G(F){var _=F.detail.item;F.detail.drawn&&a.layoutInstance.ondraw&&(F.detail.drawn.gx+=l.x/E,F.detail.drawn.gy+=l.y/E,a.layoutInstance.ondraw(_[0],_[1],_[2],F.detail.drawn))}T.addEventListener("wordclouddrawn",G),a.layoutInstance&&a.layoutInstance.dispose(),a.layoutInstance={ondraw:null,dispose:function(){T.removeEventListener("wordclouddrawn",G),T.addEventListener("wordclouddrawn",function(F){F.preventDefault()})}}})}),O.registerPreprocessor(function(f){var o=(f||{}).series;!O.util.isArray(o)&&(o=o?[o]:[]);var a=["shadowColor","shadowBlur","shadowOffsetX","shadowOffsetY"];O.util.each(o,function(t){if(t&&t.type==="wordCloud"){var x=t.textStyle||{};l(x.normal),l(x.emphasis)}});function l(t){t&&O.util.each(a,function(x){t.hasOwnProperty(x)&&(t["text"+O.format.capitalFirst(x)]=t[x])})}});function Ct(f,o){var a=f.width,l=f.height;a>l*o?(f.x+=(a-l*o)/2,f.width=l*o):(f.y+=(l-a/o)/2,f.height=a/o)}},"echarts/lib/echarts":L=>{L.exports=$}},R={};function j(L){if(R[L])return R[L].exports;var K=R[L]={exports:{}};return Y[L](K,K.exports,j),K.exports}return j.r=L=>{typeof Symbol<"u"&&Symbol.toStringTag&&Object.defineProperty(L,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(L,"__esModule",{value:!0})},j("./index.js")})()})})(Tt);var Mt=Tt.exports;const Gt=Lt(Mt),Nt=Xt({__proto__:null,default:Gt},[Mt]);export{Nt as e};

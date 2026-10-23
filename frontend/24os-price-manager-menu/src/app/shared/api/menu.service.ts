import { Injectable } from '@angular/core';
import { Observable, Subject, tap } from 'rxjs';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class MenuService {

  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/menu/';
  private url_style = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/style-menu/';
  private url_image = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/image-menu/';
  private url_renumber = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/renumber-menu/';
  private url_inc_price = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/inc-price-menu/';

  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/menu/';
  // private url_style = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/style-menu/';
  // private url_image = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/image-menu/';
  // private url_renumber = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/renumber-menu/';
  // private url_inc_price = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/inc-price-menu/';

  private _refreshRequired = new Subject<void>();
  get requiredRefresh(){
    return this._refreshRequired;
  }
  constructor(private httpClient: HttpClient) { }

  getMenuById(menu_id:any){
    return this.httpClient.get(this.url + menu_id);
  }

  getStyleMenu(menu_id:any){
    return this.httpClient.get(this.url_style + menu_id);
  }

  setStyleMenu(menu_id:any, style:any){
    return this.httpClient.post(this.url_style + menu_id, {style:style});
  }

  renumberMenu(menu_id:any){
    return this.httpClient.get(this.url_renumber + menu_id);
  }

  incPrice(menu_id:any, inc:number, type:string){
    return this.httpClient.post(this.url_inc_price + menu_id, {type:type, inc:inc}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  getOptionImageMenu(menu_id:any){
    return this.httpClient.get(this.url_image + menu_id);
  }
  setOptionImageMenu(menu_id:any, option:any){
    return this.httpClient.post(this.url_image + menu_id, {option:option}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }
}

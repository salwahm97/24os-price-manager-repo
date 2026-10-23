import { Injectable } from '@angular/core';
import { Observable, Subject, tap } from 'rxjs';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ItemsService {
  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/categories-and-items/';
  private cat_url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/categories/';
  private cat_sorting_url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/sorting-categories/';
  private items_url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/items/';
  private items_sorting_url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/sorting-items/';

  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/categories-and-items/';
  // private cat_url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/categories/';
  // private cat_sorting_url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/sorting-categories/';
  // private items_url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/items/';
  // private items_sorting_url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/sorting-items/';

  private _refreshRequired = new Subject<void>();
  get requiredRefresh(){
    return this._refreshRequired;
  }
  constructor(private httpClient: HttpClient) { }

  getMenuCategoriesAndItems(menu_id:any){
    return this.httpClient.get(this.url + menu_id);
  }

  getCategoryById(id:any){
    return this.httpClient.get(this.cat_url + id);
  }

  getItemsByCategoryId(category_id:any){
    return this.httpClient.get(this.items_url + category_id);
  }

  insertCategory(inputData:any, menu_id:number, index:number, preview:string){
    return this.httpClient.post(this.cat_url, {data:inputData, menu_id:menu_id, index:index, preview:preview}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  updateCategory(inputData:any, id: any, menu_id:number, preview:string){
    return this.httpClient.patch(this.cat_url + id, {data:inputData, menu_id:menu_id, preview:preview}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  deleteCategory(id: any){
    return this.httpClient.delete(this.cat_url + id).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  sortingCategories(sorting :any[]){
    return this.httpClient.post(this.cat_sorting_url, {sorting:sorting}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }



  insertItem(inputData:any, category_id:number, index:Number, menu_id:any, preview:string){
    return this.httpClient.post(this.items_url, {data:inputData, category_id:category_id, index:index, menu_id:menu_id, preview:preview}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  updateItem(inputData:any, id: any, category_id:number, menu_id:any, preview:string){
    return this.httpClient.patch(this.items_url + id, {data:inputData, category_id:category_id, menu_id:menu_id, preview:preview}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  deleteItem(id: any){
    return this.httpClient.delete(this.items_url + id);
  }

  sortingItems(sorting :any[]){
    return this.httpClient.post(this.items_sorting_url, {sorting:sorting});
  }
}

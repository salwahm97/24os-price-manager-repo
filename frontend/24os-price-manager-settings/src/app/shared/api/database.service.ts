import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DatabaseService {

  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/database/';
  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/database/';

  constructor(private httpClient: HttpClient) { }

  resetDatabase(){
    return this.httpClient.get(this.url);
  }
}

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class StatisticsService {
  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/statistics/';
  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/statistics/';

  constructor(private httpClient: HttpClient) { }

  getStatistics(menu_id:any)
  {
    return this.httpClient.get(this.url + menu_id);
  }
}

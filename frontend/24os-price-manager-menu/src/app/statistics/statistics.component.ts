import { animate, state, style, transition, trigger } from '@angular/animations';
import { Component, OnInit } from '@angular/core';
import { StatisticsService } from '../shared/api/statistics.service';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-statistics',
  templateUrl: './statistics.component.html',
  styleUrls: ['./statistics.component.css'],
  animations: [
    trigger('detailExpand', [
      state('collapsed', style({height: '0px', minHeight: '0'})),
      state('expanded', style({height: '*'})),
      transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
    ]),
  ],
})
export class StatisticsComponent implements OnInit {
  dataSource !:any;
  columnsToDisplay = ['Agent', 'IP_Address', 'Date', 'Visits'];
  columnsToDisplayWithExpand = [...this.columnsToDisplay, 'expand'];
  expandedElement!: any | null;

  menu_id :any;
  total_visits_count !: number;
  visitors_count!: number;

  screen_loader !: boolean;
  responseData :any;
  responseLang :any;

  constructor(
    private service:StatisticsService,
    private translate : TranslateService,
    private serviceLang:LanguageService,
  )
  {
    this.translate.addLangs(['en', 'de']);
    this.translate.setDefaultLang('en');
  }

  ngOnInit(): void {

    this.serviceLang.getLang().subscribe(response => {
      this.responseLang = response;
      this.translate.use(this.responseLang);
    });

    this.serviceLang.requiredRefresh.subscribe(r=>{
      this.serviceLang.getLang().subscribe(response => {
        this.responseLang = response;
        this.translate.use(this.responseLang);
      });
    });


    let paramsString = window.location.search;
    let searchParams = new URLSearchParams(paramsString);
    this.menu_id = Number(searchParams.get("menu_id"));
    if(Number.isInteger(this.menu_id) && this.menu_id > 0){
      this.getStatistics(this.menu_id);
    }
  }
  getStatistics(menu_id:any) {
    this.screen_loader = true;
    this.service.getStatistics(menu_id).subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;

      this.total_visits_count = this.responseData.total_visits_count;
      this.visitors_count = this.responseData.visitors_count;

      var data: { IP_Address: any; Date: any; Agent: any; Visits: any; old_visits: { agent: any; date: any; }[]; }[] = [];

      this.responseData.visitors_visit_last.forEach((element: { visitor_old_visits: { visitor_agent: any; visit_date: any; }[]; visitor_visit_last: { visitor_IP: any; visit_date: any; visitor_agent: any; }; visitor_visits_count: any; }) => {
        var old_visits: { agent: any; date: any; }[] = [];
        element.visitor_old_visits.forEach((old: { visitor_agent: any; visit_date: any; }) => {
          old_visits.push({ agent : old.visitor_agent, date : old.visit_date });
        });

        data.push({
          IP_Address: element.visitor_visit_last.visitor_IP,
          Date: element.visitor_visit_last.visit_date,
          Agent: element.visitor_visit_last.visitor_agent,
          Visits: element.visitor_visits_count,
          old_visits: old_visits
        });


      });
      this.dataSource = data;

    });
  }


}


import { Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { RecoveryService } from '../shared/api/recovery.service';
import { menuModel } from '../model/menu-model';
import { IndividualConfig } from 'ngx-toastr';
import { LanguageService } from '../shared/language.service';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-recovery',
  templateUrl: './recovery.component.html',
  styleUrls: ['./recovery.component.css']
})
export class RecoveryComponent implements OnInit {

  menuData!: MatTableDataSource<menuModel>;
  displayedColumns: string[] = ['position', 'menu', 'actions'];
  responseData:any;
  menusCount:any;
  trashMenusCount:any;
  allMenusCount:any;

  @ViewChild(MatPaginator)
  paginator!: MatPaginator;
  @ViewChild(MatSort)
  sort!: MatSort;

  responseLang :any;
  toast!: toastrPayload;
  screen_loader!:boolean;

  constructor(
    private translate : TranslateService,
    private service:RecoveryService,
    private cs: NotificationService,
    private serviceLang:LanguageService,
  )
  {
    this.translate.addLangs(['en', 'de']);
    this.translate.setDefaultLang('en');
  }

  ngOnInit(): void {
    this.getTrashMenus();
    this.service.requiredRefresh.subscribe(r=>{
      this.getTrashMenus();
    });

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
  }

  getTrashMenus(){
    this.screen_loader = true;
    this.service.getTrashMenus().subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;
      this.menuData = new MatTableDataSource(this.responseData.menus);
      this.menuData.paginator = this.paginator;
      this.menuData.sort = this.sort;

      this.menusCount = this.responseData.counts.menusCount;
      this.trashMenusCount = this.responseData.counts.trashMenusCount;
      this.allMenusCount = this.responseData.counts.allMenusCount;
    });
  }

  publishMenus(page: string){
    window.location.assign(
      window.location.href.split("?")[0]+'?page='+page
    );
  }

  trashMenus(page: string){
    window.location.assign(
      window.location.href.split("?")[0]+'?page='+page
    );
  }

  confirmDelete(id:any, menu:string): void {
    var deleteConfirm= confirm("Are you sure to delete "+menu+" finally?");
    if(deleteConfirm){
      this.screen_loader = true;
      this.service.deleteMenu(id).subscribe(response => {
        this.screen_loader = false;
        this.responseData = response;
        if( this.responseData.message == 'menu deleted successfully')
        {
          if(this.responseData.lang == 'en')
          {
            this.toastShow('success', 'Success', 'Menu Deleted Successfully');

          }
          else{
            this.toastShow('success', 'Erfolg', 'Menü Erfolgreich Gelöscht');
          }
        }

      });
    }

  }

  confirmRestore(id:any, menu:string): void {
    var restoreConfirm= confirm("Are you sure to restore "+menu+"?");
    if(restoreConfirm){
      this.screen_loader = true;
      this.service.restoreMenu(id).subscribe(response => {
        this.screen_loader = false;
        this.responseData = response;
        if(this.responseData.message == 'menu restored successfully')
        {
          if(this.responseData.lang == 'en')
          {
            this.toastShow('success', 'Success', 'Menu Restored Successfully');

          }
          else{
            this.toastShow('success', 'Erfolg', 'Menü Erfolgreich Wiederhergestellt');
          }
        }

      });
    }

  }
applyFilter(event: Event) {
  const filterValue = (event.target as HTMLInputElement).value;
  this.menuData.filter = filterValue.trim().toLowerCase();

  if (this.menuData.paginator) {
    this.menuData.paginator.firstPage();
  }
}

toastShow(type: string, title: string, message:string){
  this.toast = {
    message: message,
    title: title,
    type: type,
    ic:{
      timeOut: 2500,
      positionClass: "toast-bottom-right",
    } as IndividualConfig,
  };
  this.cs.showToast(this.toast);
}

}

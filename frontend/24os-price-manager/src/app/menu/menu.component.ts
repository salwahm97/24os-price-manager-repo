import { Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MenuPopupComponent } from '../menu-popup/menu-popup.component';
import { MenuService } from '../shared/api/menu.service';

import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { menuModel } from '../model/menu-model';

import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { MatSlideToggleChange } from '@angular/material/slide-toggle';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-menu',
  templateUrl: './menu.component.html',
  styleUrls: ['./menu.component.css']
})
export class MenuComponent implements OnInit {
  menuData!: MatTableDataSource<menuModel>;
  displayedColumns: string[] = ['position', 'menu', 'catCount', 'itemsCount', 'actions'];
  responseData:any;
  responseLang :any;
  menusCount:any;
  trashMenusCount:any;
  allMenusCount:any;

  @ViewChild(MatPaginator)
  paginator!: MatPaginator;
  @ViewChild(MatSort)
  sort!: MatSort;

  menuDataById:any;
  menuType:string[] = ['Uncategorized Price Menu'];
  toast!: toastrPayload;
  screen_loader!:boolean;

  constructor(
    private translate : TranslateService,
    private menuDialog: MatDialog,
    private service:MenuService,
    private serviceLang:LanguageService,
    private cs: NotificationService) {

      this.translate.addLangs(['en', 'de']);
      this.translate.setDefaultLang('en');
    }

  ngOnInit(): void {
    this.getMenus();
    this.service.requiredRefresh.subscribe(r=>{
      this.getMenus();
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

  getMenus(){
    this.screen_loader = true;
    this.service.getMenus().subscribe(response => {
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

  onToggle(event: MatSlideToggleChange, id:any){
    this.screen_loader = true;
    this.service.activeMenu(event.checked, id).subscribe(response => {
      this.responseData = response;
      this.screen_loader = false;

      if( this.responseData.st == "activated")
      {
        if(this.responseData.lang == "en")
        {
          this.toastShow('success', 'Success', "Menu Activated Successfully");
        }
        else{
          this.toastShow('success', 'Erfolg', "Menü Erfolgreich Aktiviert");
        }
      }
      if( this.responseData.st == "unactivated")
      {
        if(this.responseData.lang == "en")
        {
          this.toastShow('success', 'Success', "Menu Unactivated Successfully");
        }
        else{
          this.toastShow('success', 'Erfolg', "Menü Erfolgreich deaktiviert");
        }
      }

    });

  }
  openMenuPopupAdd(action:string, id=0): void {
    const dialogRef = this.menuDialog.open(MenuPopupComponent, {
      width:'400px',
      panelClass: 'fullscreen-dialog',
      data:{title:"Create New Menu", action:action, id:0, menu:'', menuType:this.menuType},
    });

    dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed ');
    });
  }

  openMenuPopupEdit(action:string, id:any): void {

    this.service.getMenuById(id).subscribe(response => {
      this.menuDataById = response;
      const dialogRef = this.menuDialog.open(MenuPopupComponent, {
        width:'400px',
        panelClass: 'fullscreen-dialog',
        data:{title:"Edit Menu", action:action, id:id, menu:this.menuDataById.menu, menuType:this.menuDataById.menu_types},
      });

    });
  }

  confirmTrash(id:any, menu:string): void {
      var trashConfirm= confirm("Are you sure to trash "+menu+"?");
      if(trashConfirm){
        this.screen_loader = true;
        this.service.trashMenu(id).subscribe(response => {
          this.responseData = response;
          this.screen_loader = false;
          if(this.responseData.message == 'menu trashed successfully')
          {
            if(this.responseData.lang == 'en')
            {
              this.toastShow('success', 'Success', 'Menu Trashed Successfully');

            }
            else{
              this.toastShow('success', 'Erfolg', 'Menü Erfolgreich Gelöscht');
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

  clickMenuRow(row: any, page: string) {
    window.location.assign(
      window.location.href.split("?")[0]+'?page='+page+'&menu_id='+row.id
    );
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

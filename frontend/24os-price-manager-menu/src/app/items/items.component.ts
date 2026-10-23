import { Component, OnInit, ViewChild } from '@angular/core';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { ItemsService } from '../shared/api/items.service';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { CategoryPopupComponent } from '../category-popup/category-popup.component';
import { MatDialog } from '@angular/material/dialog';
import { IndividualConfig } from 'ngx-toastr';
import { ItemPopupComponent } from '../item-popup/item-popup.component';
import { MatAccordion } from '@angular/material/expansion';
import { MenuService } from '../shared/api/menu.service';

import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-items',
  templateUrl: './items.component.html',
  styleUrls: ['./items.component.css']
})
export class ItemsComponent implements OnInit {
  @ViewChild(MatAccordion) accordion !: MatAccordion;

  toast!: toastrPayload;
  screen_loader!:boolean;

  categories :any;
  categoryItems :any;
  responseData :any;
  menu_id !: any;
  categoryDataById : any;
  sort!: boolean;
  expand!: boolean;

  category_image !: boolean;
  item_image !: boolean;

  responseLang :any;

  constructor(
    private categoryDialog: MatDialog,
    private itemDialog: MatDialog,
    private service:ItemsService,
    private service2:MenuService,
    private cs: NotificationService,
    private translate : TranslateService,
    private serviceLang:LanguageService,
  )
  {
    this.translate.addLangs(['en', 'de']);
    this.translate.setDefaultLang('en');
  }

  ngOnInit(): void {

    let paramsString = window.location.search;
    let searchParams = new URLSearchParams(paramsString);
    this.menu_id = Number(searchParams.get("menu_id"));
    if(Number.isInteger(this.menu_id) && this.menu_id > 0){
      this.getMenuCategoriesAndItems(this.menu_id);
      this.service.requiredRefresh.subscribe(r=>{
        this.getMenuCategoriesAndItems(this.menu_id);
      });

      this.getOptionImageMenu(this.menu_id);
      this.service2.requiredRefresh.subscribe(r=>{
        this.getOptionImageMenu(this.menu_id);
      });
    }


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

  getOptionImageMenu(menu_id:any){
    this.service2.getOptionImageMenu(menu_id).subscribe(response => {
      this.screen_loader = false;

      if(response == 'without_image')
      {
        this.category_image = false;
        this.item_image = false;
      }
      else if(response == 'category_image')
      {
        this.category_image = true;
        this.item_image = false;

      }
      else if(response == 'item_image')
      {
        this.category_image = false;
        this.item_image = true;
      }
    });
  }

  optionSort(flag:boolean){
    this.sort = flag;
  }
  optionExpand(flag:boolean){
    this.expand = flag;
    if(flag)
    {
      this.accordion.openAll();
    }
    else{
      this.accordion.closeAll();
    }
  }

  getMenuCategoriesAndItems(menu_id:any){
    this.screen_loader = true;
    this.service.getMenuCategoriesAndItems(menu_id).subscribe(response => {
      this.categories = response;
      this.screen_loader = false;
    });
  }

  openCategoryPopupAdd(action:string, menu_id: number): void {
    if(this.category_image != undefined){
      let lastindex = this.categories.length - 1;
      const dialogRef = this.categoryDialog.open(CategoryPopupComponent, {
        width:'400px',
        panelClass: 'fullscreen-dialog',
        data:{
          title:"Create New Category",
          action:action,
          id:0,
          menu_id: menu_id,
          category_name:'',
          category_desc:'',
          index:lastindex,
          category_image:this.category_image,
          preview : location.href.split("wp-admin")[0] +'wp-content/plugins/24os-price-manager/assets/image.png'
        },
      });

      dialogRef.afterClosed().subscribe(result => {
        console.log('The dialog was closed ');
      });
    }

  }

  openCategoryPopupEdit(
    action:string,
    menu_id: number,
    id:any,
    category_name:string,
    category_desc:string,
    index:number,
    category_img:string
  ): void {
    if(this.category_image != undefined){

      const dialogRef = this.categoryDialog.open(CategoryPopupComponent, {
        width:'400px',
        panelClass: 'fullscreen-dialog',
        data:{
          title:"Edit Category",
          action:action,
          id:id,
          menu_id:menu_id,
          category_name:category_name,
          category_desc:category_desc,
          index:index,
          category_image:this.category_image,
          preview: category_img
        },
      });
    }
  }

  confirmCategoryDelete(id:any, category:string): void {
    var deleteConfirm= confirm("Are you sure to delete category ("+category+") ?");
    if(deleteConfirm){
      this.screen_loader = true;
      this.service.deleteCategory(id).subscribe(response => {
        this.screen_loader = false;
        this.responseData = response;
        if(this.responseData.message == 'category deleted successfully')
        {
          if(this.responseData.lang == 'en')
          {
            this.toastShow('success', 'Success', 'Category Deleted Successfully');
          }
          else
          {
            this.toastShow('success', 'Erfolg', 'Kategorie Erfolgreich Gelöscht');
          }
        }
      });
    }
  }

  dropCat(event: CdkDragDrop<any[]>, categories:any) {
    if(this.sort)
    {
      moveItemInArray(categories, event.previousIndex, event.currentIndex);
      var sorting = new Array();

      if(event.previousIndex - event.currentIndex > 0){

        let dec = event.previousIndex - event.currentIndex;
        let moved_element_index = Number(categories[event.currentIndex].index) - dec;
        for (let index = event.currentIndex; index <= event.previousIndex; index++) {
          const element = categories[index];
          if(categories[event.currentIndex].category_id != element.category_id){
            if(event.previousIndex != element.index){
              sorting.push({id:Number(element.category_id), index:(Number(element.index) + 1)});
            }
          }
        }
        sorting.push({id:Number(categories[event.currentIndex].category_id), index:moved_element_index});
      }
      else if(event.previousIndex - event.currentIndex < 0){
        let dec = event.currentIndex - event.previousIndex;
        let moved_element_index = Number(categories[event.currentIndex].index) + dec;
        for (let index = event.previousIndex; index <= event.currentIndex; index++) {
          const element = categories[index];
          if(categories[event.currentIndex].category_id != element.category_id){
            if(event.previousIndex != element.index){
              sorting.push({id:Number(element.category_id), index:(Number(element.index) - 1)});

            }
          }
        }
        sorting.push({id:Number(categories[event.currentIndex].category_id), index:moved_element_index});
      }

      this.sortingCategories(sorting);
    }

  }
  sortingCategories(sorting: any[]) {
    this.screen_loader = true;
    this.service.sortingCategories(sorting).subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;
      if(this.responseData.message == 'categories sorting updated successfully'){
        if(this.responseData.lang == 'en')
        {
          this.toastShow('success', 'Success', 'Categories Sorting Updated Successfully');
        }
        else{
          this.toastShow('success', 'Erfolg', 'Kategoriensortierung Erfolgreich Aktualisiert');
        }
      }

    });
  }


  openItemPopupAdd(action:string, category_id: number): void {
    if(this.item_image != undefined){

      this.service.getItemsByCategoryId(category_id).subscribe(response => {
        this.categoryItems = response;
        let lastindex = this.categoryItems.length - 1;

        const dialogRef = this.itemDialog.open(ItemPopupComponent, {
          width:'500px',
          panelClass: 'fullscreen-item-dialog',
          data:{
            title:"Create New Item",
            action:action,
            id:0,
            category_id: category_id,
            item_num:0,
            item_name:'',
            item_desc:'',
            item_allergens:'',
            item_prices:'',
            index:lastindex,
            item_image:this.item_image,
            preview : location.href.split("wp-admin")[0] +'wp-content/plugins/24os-price-manager/assets/image.png'
          },
        });

      });
    }
  }

  openItemPopupEdit(
    action:string,
    category_id:number,
    item_id:any,
    item_num:number,
    item_name:string,
    item_desc:string,
    item_allergens:any,
    item_prices:any,
    index:number,
    item_img:string
  ): void {
    if(this.item_image != undefined){
      const dialogRef = this.itemDialog.open(ItemPopupComponent, {
        width:'500px',
        panelClass: 'fullscreen-item-dialog',
        data:{
          title:"Edit Item",
          action:action,
          id:item_id,
          category_id: category_id,
          item_num:item_num,
          item_name:item_name,
          item_desc:item_desc,
          item_allergens:item_allergens,
          item_prices:item_prices,
          index:index,
          item_image:this.item_image,
          preview: item_img
        }
      });
    }
  }

  confirmItemDelete(id:any, item:string, i:number, array:any): void {
    var deleteConfirm= confirm("Are you sure to delete item ("+item+") ?");
    if(deleteConfirm){
      this.screen_loader = true;
      this.service.deleteItem(id).subscribe(response => {
        this.screen_loader = false;
        this.responseData = response;
        if(this.responseData.message == 'item deleted successfully')
        {
          array.splice(i, 1);
          if(this.responseData.lang == 'en')
          {
            this.toastShow('success', 'Success', 'Item Deleted Successfully');
          }
          else{
            this.toastShow('success', 'Erfolg', 'Artikel Erfolgreich Gelöscht');
          }
        }

      });
    }
  }


  dropItem(event: CdkDragDrop<any[]>, items :any) {
    if(this.sort){
      moveItemInArray(items, event.previousIndex, event.currentIndex);
      var sorting = new Array();

      if(event.previousIndex - event.currentIndex > 0){

        let dec = event.previousIndex - event.currentIndex;
        let moved_element_index = Number(items[event.currentIndex].index) - dec;
        for (let index = event.currentIndex; index <= event.previousIndex; index++) {
          const element = items[index];
          if(items[event.currentIndex].item_id != element.item_id){
            if(event.previousIndex != element.index){
              sorting.push({id:Number(element.item_id), index:(Number(element.index) + 1)});
            }
          }
        }
        sorting.push({id:Number(items[event.currentIndex].item_id), index:moved_element_index});
      }
      else if(event.previousIndex - event.currentIndex < 0){
        let dec = event.currentIndex - event.previousIndex;
        let moved_element_index = Number(items[event.currentIndex].index) + dec;
        for (let index = event.previousIndex; index <= event.currentIndex; index++) {
          const element = items[index];
          if(items[event.currentIndex].item_id != element.item_id){
            if(event.previousIndex != element.index){
              sorting.push({id:Number(element.item_id), index:(Number(element.index) - 1)});

            }
          }
        }
        sorting.push({id:Number(items[event.currentIndex].item_id), index:moved_element_index});
      }

      this.sortingItems(sorting);
    }

  }

  sortingItems(sorting: any[]) {
    this.screen_loader = true;
    this.service.sortingItems(sorting).subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;
      if( this.responseData.message == 'items sorting updated successfully'){
        if(this.responseData.lang == 'en')
        {
          this.toastShow('success', 'Success', 'Items Sorting Updated Successfully');
        }
        else{
          this.toastShow('success', 'Erfolg', 'Artikelsortierung erfolgreich aktualisiert');
        }

      }

    });
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



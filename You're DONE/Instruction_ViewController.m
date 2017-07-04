//
//  Instruction ViewController.m
//  You're DONE
//
//  Created by Leo Lorenz on 1/29/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import "Instruction ViewController.h"
#import "ViewController.h"


@interface Instruction_ViewController ()
@property (weak, nonatomic) IBOutlet UIButton *acceptButton;
@property (weak, nonatomic) IBOutlet UIButton *declineButton;

@end

@implementation Instruction_ViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    // Do any additional setup after loading the view.
    
    [self.navigationController.navigationBar setHidden:YES];
    
    self.acceptButton.layer.borderWidth = 0;
    self.acceptButton.layer.cornerRadius = 7.0;
    self.acceptButton.layer.masksToBounds = YES;
    self.declineButton.layer.borderWidth = 0;
    self.declineButton.layer.cornerRadius = 7.0;
    self.declineButton.layer.masksToBounds = YES;

}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}
- (IBAction)okButtonClicked:(id)sender {
    
    
    ViewController * vc = (ViewController*)[[UIStoryboard storyboardWithName:@"Main" bundle:nil] instantiateViewControllerWithIdentifier:@"byetalk"];
    [self.navigationController pushViewController:vc animated:YES];
}

- (IBAction)declineButtonClicked:(id)sender {
    
    exit(0);
}

@end
